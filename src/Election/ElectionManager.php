<?php

namespace AppBundle\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\CityVoteResult;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Entity\VoteResult;
use AppBundle\Repository\CityVoteResultRepository;
use AppBundle\Repository\Election\VoteResultListCollectionRepository;
use AppBundle\Repository\ElectionRepository;
use AppBundle\Repository\VoteResultRepository;
use Doctrine\ORM\EntityManagerInterface;

class ElectionManager
{
    private $entityManager;
    private $electionRepository;
    private $voteResultRepository;
    private $listCollectionRepository;
    private $cityVoteResultRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ElectionRepository $electionRepository,
        VoteResultRepository $voteResultRepository,
        CityVoteResultRepository $cityVoteResultRepository,
        VoteResultListCollectionRepository $listCollectionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->electionRepository = $electionRepository;
        $this->voteResultRepository = $voteResultRepository;
        $this->cityVoteResultRepository = $cityVoteResultRepository;
        $this->listCollectionRepository = $listCollectionRepository;
    }

    public function getClosestElectionRound(): ?ElectionRound
    {
        if (!$election = $this->electionRepository->findClosestElection()) {
            return null;
        }

        $now = new \DateTime();

        $selectedRound = $election->getRounds()->current();
        $days = $selectedRound->getDate()->diff($now)->days;

        foreach ($election->getRounds() as $round) {
            if (($tmp = $round->getDate()->diff($now)->days) < $days) {
                $selectedRound = $round;
                $days = $tmp;
            }
        }

        return $selectedRound;
    }

    public function getVoteResultForCurrentElectionRound(VotePlace $votePlace): ?VoteResult
    {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        $voteResult = $this->voteResultRepository->findOneForVotePlace($votePlace, $round) ?? new VoteResult($votePlace, $round);

        $listsCollection = $this->listCollectionRepository->findOneByCityInseeCode($votePlace->getInseeCode());

        if ($listsCollection) {
            $voteResult->updateLists($listsCollection);

            if (!$voteResult->getId()) {
                $this->entityManager->persist($voteResult);
            }

            $this->entityManager->flush();
        }

        return $voteResult;
    }

    public function getCityVoteResultForCurrentElectionRound(City $city): ?CityVoteResult
    {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        return $this->cityVoteResultRepository->findOneForCity($city, $round) ?? new CityVoteResult($city, $round);
    }
}
