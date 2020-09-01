<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\CommitteeElection;
use App\Entity\TerritorialCouncil\Election as TerritorialCouncilElection;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionPoolTitleEnum;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VotersList;
use App\Repository\CommitteeCandidacyRepository;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\CandidacyRepository as TerritorialCouncilCandidacyRepository;
use App\Repository\TerritorialCouncil\ElectionRepository as TerritorialCouncilElectionRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Events;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteIsOpenEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class VotingPlatformConfigureCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:configure';

    /** @var DesignationRepository */
    private $designationRepository;
    /** @var CommitteeElectionRepository */
    private $committeeElectionRepository;
    /** @var SymfonyStyle */
    private $io;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ElectionRepository */
    private $electionRepository;
    /** @var CommitteeCandidacyRepository */
    private $committeeCandidacyRepository;
    /** @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;
    /** @var VoterRepository */
    private $voterRepository;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var TerritorialCouncilElectionRepository */
    private $territorialCouncilElectionRepository;
    /** @var TerritorialCouncilCandidacyRepository */
    private $territorialCouncilCandidacyRepository;
    /** @var TranslatorInterface */
    private $translator;

    protected function configure()
    {
        $this
            ->setDescription('Configure Voting Platform: create Election and voters/candidates lists')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in minutes for designation selection (1 min by default)', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingDesignations(
            $date->modify(sprintf('+%d minutes', (int) $input->getOption('interval')))
        );

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->configureCommitteeElections($designation);
            } elseif (DesignationTypeEnum::COPOL === $designation->getType()) {
                $this->configureCopolElections($designation);
            } else {
                $this->io->error(sprintf('Unhandled designation type "%s"', $designation->getType()));
            }
        }

        $this->io->progressFinish();
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        $offset = 0;

        while ($committeeElections = $this->committeeElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($committeeElections as $committeeElection) {
                $committee = $committeeElection->getCommittee();

                if ($this->electionRepository->hasElectionForCommittee($committee, $designation)) {
                    continue;
                }

                if (!$this->isValidCommitteeElection($committeeElection, $designation)) {
                    continue;
                }

                $this->io->progressAdvance();

                $election = new Election(
                    $this->entityManager->getPartialReference(Designation::class, $designation->getId()),
                    null,
                    [new ElectionRound()]
                );
                $election->setElectionEntity(new ElectionEntity($committee));

                $this->configureNewElectionForCommittee($election, $designation);
            }

            $this->entityManager->clear();

            $designation = $this->entityManager->merge($designation);

            $offset += \count($committeeElections);
        }
    }

    private function configureCopolElections(Designation $designation): void
    {
        $offset = 0;

        while ($territorialCouncilElections = $this->territorialCouncilElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($territorialCouncilElections as $coTerrElection) {
                $coTerr = $coTerrElection->getTerritorialCouncil();

                if ($this->electionRepository->hasElectionForTerritorialCouncil($coTerr, $designation)) {
                    continue;
                }

                if (!$this->isValidTerritorialCouncilElection($coTerrElection)) {
                    continue;
                }

                $this->io->progressAdvance();

                $election = new Election(
                    $this->entityManager->getPartialReference(Designation::class, $designation->getId()),
                    null,
                    [new ElectionRound()]
                );
                $election->setElectionEntity(new ElectionEntity(null, $coTerr));

                $this->configureNewElectionForTerritorialCouncil($election, $designation, $coTerrElection);
            }

            $this->entityManager->clear();

            $designation = $this->entityManager->merge($designation);

            $offset += \count($territorialCouncilElections);
        }
    }

    private function configureNewElectionForTerritorialCouncil(
        Election $election,
        Designation $designation,
        TerritorialCouncilElection $coTerrElection
    ): void {
        $electionRound = $election->getCurrentRound();
        $coTerr = $coTerrElection->getTerritorialCouncil();

        // Create candidates groups
        $candidacies = $this->territorialCouncilCandidacyRepository->findAllConfirmedForElection($coTerrElection);

        $pools = [];

        foreach ($candidacies as $candidacy) {
            if ($candidacy->isTaken()) {
                continue;
            }

            $adherent = $candidacy->getMembership()->getAdherent();

            $group = new CandidateGroup();
            $group->addCandidate($candidate = new Candidate(
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $candidacy->getGender(),
                $adherent
            ));

            $candidate->setImagePath($candidacy->getImagePath());
            $candidate->setBiography($candidacy->getBiography());
            $candidate->setFaithStatement($candidacy->getFaithStatement());
            $candidacy->take();

            if ($binome = $candidacy->getBinome()) {
                $adherent = $binome->getMembership()->getAdherent();

                $group->addCandidate($candidate = new Candidate(
                    $adherent->getFirstName(),
                    $adherent->getLastName(),
                    $binome->getGender(),
                    $adherent
                ));

                $candidate->setImagePath($binome->getImagePath());
                $candidate->setBiography($binome->getBiography());
                $candidate->setFaithStatement($binome->getFaithStatement());
                $binome->take();
            }

            if (!isset($pools[$candidacy->getQuality()])) {
                $pools[$candidacy->getQuality()] = new ElectionPool($this->translator->trans('territorial_council.membership.quality.'.$candidacy->getQuality()));
            }

            $pools[$candidacy->getQuality()]->addCandidateGroup($group);
        }

        foreach ($pools as $pool) {
            if ($pool->getCandidateGroups()) {
                $electionRound->addElectionPool($pool);
                $election->addElectionPool($pool);
            }
        }

        $list = new VotersList($election);

        $adherents = [];

        foreach ($coTerr->getMemberships() as $membership) {
            $adherents[] = $adherent = $membership->getAdherent();

            $list->addVoter($this->voterRepository->findForAdherent($adherent) ?? new Voter($adherent));
        }

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();
    }

    private function configureNewElectionForCommittee(Election $election, Designation $designation): void
    {
        $electionRound = $election->getCurrentRound();
        $committee = $election->getElectionEntity()->getCommittee();

        // Create candidates groups
        $candidacies = $this->committeeCandidacyRepository->findByCommittee($committee, $election->getDesignation());

        $womanPool = new ElectionPool(ElectionPoolTitleEnum::WOMAN);
        $manPool = new ElectionPool(ElectionPoolTitleEnum::MAN);

        foreach ($candidacies as $candidacy) {
            $adherent = $candidacy->getCommitteeMembership()->getAdherent();

            $group = new CandidateGroup();
            $group->addCandidate($candidate = new Candidate(
                $adherent->getFirstName(),
                $adherent->getLastName(),
                $candidacy->getGender(),
                $adherent
            ));

            $candidate->setImagePath($candidacy->getImagePath());
            $candidate->setBiography($candidacy->getBiography());

            if ($candidate->isWoman()) {
                $womanPool->addCandidateGroup($group);
            } else {
                $manPool->addCandidateGroup($group);
            }
        }

        if ($womanPool->getCandidateGroups()) {
            $electionRound->addElectionPool($womanPool);
            $election->addElectionPool($womanPool);
        }

        if ($manPool->getCandidateGroups()) {
            $electionRound->addElectionPool($manPool);
            $election->addElectionPool($manPool);
        }

        $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee);

        $list = new VotersList($election);

        $adherents = [];

        foreach ($memberships as $membership) {
            $adherents[] = $adherent = $membership->getAdherent();

            $list->addVoter($this->voterRepository->findForAdherent($adherent) ?? new Voter($adherent));
        }

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        $this->notifyVoters($adherents, $designation, function (Adherent $adherent, Designation $designation) use ($committee) {
            return new CommitteeElectionVoteIsOpenEvent($adherent, $designation, $committee);
        });
    }

    private function isValidCommitteeElection(CommitteeElection $committeeElection, Designation $designation): bool
    {
        $committee = $committeeElection->getCommittee();

        // validate voters
        if (!$this->committeeMembershipRepository->committeeHasVoters($committee)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('Committee "%s" does not have any voters', $committee->getSlug()));
            }

            return false;
        }

        // validate candidatures
        $candidacies = $this->committeeCandidacyRepository->findByCommittee($committee, $designation);

        if (0 === \count($candidacies)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('Committee "%s" does not have at least 1 candidate', $committee->getSlug()));
            }

            return false;
        }

        return true;
    }

    private function isValidTerritorialCouncilElection(TerritorialCouncilElection $coTerrElection): bool
    {
        $coTerr = $coTerrElection->getTerritorialCouncil();

        // validate voters
        if ($coTerr->getMemberships()->isEmpty()) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('CoTerr "%s" does not have any voters', $coTerr->getUuid()->toString()));
            }

            return false;
        }

        // validate candidatures
        $candidacies = $this->territorialCouncilCandidacyRepository->findAllConfirmedForElection($coTerrElection);

        if (0 === \count($candidacies)) {
            if ($this->io->isDebug()) {
                $this->io->warning(sprintf('CoTerr "%s" does not have at least 1 candidate', $coTerr->getUuid()->toString()));
            }

            return false;
        }

        return true;
    }

    private function notifyVoters(array $adherents, Designation $designation, \Closure $eventFactoryCallback): void
    {
        foreach ($adherents as $adherent) {
            $this->dispatcher->dispatch(Events::VOTE_OPEN, $eventFactoryCallback($adherent, $designation));
        }
    }

    /** @required */
    public function setDesignationRepository(DesignationRepository $designationRepository): void
    {
        $this->designationRepository = $designationRepository;
    }

    /** @required */
    public function setCommitteeElectionRepository(CommitteeElectionRepository $committeeElectionRepository): void
    {
        $this->committeeElectionRepository = $committeeElectionRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setElectionRepository(ElectionRepository $electionRepository): void
    {
        $this->electionRepository = $electionRepository;
    }

    /** @required */
    public function setCommitteeCandidacyRepository(CommitteeCandidacyRepository $committeeCandidacyRepository): void
    {
        $this->committeeCandidacyRepository = $committeeCandidacyRepository;
    }

    /** @required */
    public function setCommitteeMembershipRepository(CommitteeMembershipRepository $committeeMembershipRepository): void
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    /** @required */
    public function setVoterRepository(VoterRepository $voterRepository): void
    {
        $this->voterRepository = $voterRepository;
    }

    /** @required */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /** @required */
    public function setTerritorialCouncilElectionRepository(
        TerritorialCouncilElectionRepository $territorialCouncilElectionRepository
    ): void {
        $this->territorialCouncilElectionRepository = $territorialCouncilElectionRepository;
    }

    /** @required */
    public function setTerritorialCouncilCandidacyRepository(
        TerritorialCouncilCandidacyRepository $territorialCouncilCandidacyRepository
    ): void {
        $this->territorialCouncilCandidacyRepository = $territorialCouncilCandidacyRepository;
    }

    /** @required */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
