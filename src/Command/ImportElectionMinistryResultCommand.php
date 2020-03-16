<?php

namespace AppBundle\Command;

use AppBundle\Election\ElectionManager;
use AppBundle\Election\VoteListNuanceEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Election\MinistryListTotalResult;
use AppBundle\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\Xml\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportElectionMinistryResultCommand extends Command
{
    private const INDEX_URL = 'https://elections.interieur.gouv.fr/telechargements/MUNICIPALES2020/resultatsT1/index.xml';
    private const CITY_INDEX_URL = 'https://elections.interieur.gouv.fr/telechargements/MUNICIPALES2020/resultatsT1/%s/%s000.xml';

    protected static $defaultName = 'app:election:import-ministry-results';

    /** @var HttpClientInterface */
    private $httpClient;
    /** @var SymfonyStyle */
    private $io;
    /** @var CityRepository */
    private $cityRepository;
    /** @var ElectionManager */
    private $electionManager;
    /** @var EntityManagerInterface */
    private $entityManager;

    private $errors = [];
    /** @var Adherent */
    private $author;

    /** @required */
    public function setCityRepository(CityRepository $cityRepository): void
    {
        $this->cityRepository = $cityRepository;
    }

    /** @required */
    public function setElectionManager(ElectionManager $electionManager): void
    {
        $this->electionManager = $electionManager;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->addArgument('author-id', InputArgument::REQUIRED, 'Author id (adherent)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->httpClient = HttpClient::create();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->author = $this->entityManager->getPartialReference(Adherent::class, $input->getArgument('author-id'));

        $response = $this->httpClient->request('GET', self::INDEX_URL);

        $service = new Service();
        $dpts = $service->parse($response->getContent());

        $responses = [];

        $this->io->progressStart(\count($dpts[1]['value']));

        foreach ($dpts[1]['value'] as $dpt) {
            $code = $this->getDptCode($dpt['value']);
            $responses[] = $this->httpClient->request('GET', sprintf(self::CITY_INDEX_URL, $code, $code));
        }

        foreach ($responses as $response) {
            $cities = $service->parse($response->getContent());

            foreach ($cities[1]['value'][4]['value'] as $cityAttr) {
                $this->updateResult($cities[1]['value'][0]['value'], $cityAttr);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->title('City errors');
        $this->io->table(['Errors'], array_map(function (string $error) { return [$error]; }, $this->errors));
    }

    private function getDptCode(array $dptAttr): string
    {
        foreach ($dptAttr as $attr) {
            if ('{}CodDpt3Car' === $attr['name']) {
                return $attr['value'];
            }
        }

        throw new \RuntimeException('Dpt code not found');
    }

    private function updateResult(string $dptCode, array $cityAttr): void
    {
        $cityCode = $cityAttr['value'][0]['value'];
        $cityName = $cityAttr['value'][1]['value'];
        $inseeCode = $dptCode.$cityCode;

        $city = $this->cityRepository->findByInseeCode($inseeCode);

        if (!$city) {
            $this->errors[] = 'City not found: '.$cityName.' ['.$inseeCode.']';

            return;
        }

        $voteResult = $this->electionManager->getMinistryVoteResultForCurrentElectionRound($city);

        if (!$voteResult) {
            throw new \RuntimeException('VoteResult not found');
        }

        foreach ($cityAttr['value'][9]['value'][0]['value'][6]['value'] as $data) {
            if ('{}Inscrits' === $data['name']) {
                $voteResult->setRegistered((int) $data['value'][0]['value']);
            }

            if ('{}Abstentions' === $data['name']) {
                $voteResult->setAbstentions((int) $data['value'][0]['value']);
            }

            if ('{}Exprimes' === $data['name']) {
                $voteResult->setExpressed((int) $data['value'][0]['value']);
            }

            if ('{}Votants' === $data['name']) {
                $voteResult->setParticipated((int) $data['value'][0]['value']);
            }
        }

        if ('{}Listes' === $cityAttr['value'][9]['value'][0]['value'][7]['name']) {
            foreach ($cityAttr['value'][9]['value'][0]['value'][7]['value'] as $list) {
                $listLabel = $this->getValueData('LibLisExt', $list['value'])['value'];
                $listNuance = $this->getValueData('CodNuaListe', $list['value'])['value'];
                $total = $this->getValueData('NbVoix', $list['value'])['value'];

                $listToUpdate = $voteResult->findListWithLabel($listLabel);
                if (!$listToUpdate) {
                    $listToUpdate = new MinistryListTotalResult();
                    $listToUpdate->setNuance(
                        \in_array($listNuance, VoteListNuanceEnum::getChoices(), true) ? $listNuance :
                            \in_array($tmp = ltrim($listNuance, 'L'), VoteListNuanceEnum::getChoices(), true) ? $tmp : null
                    );
                    $listToUpdate->setLabel($listLabel);
                    $voteResult->addListTotalResult($listToUpdate);
                }

                $listToUpdate->setTotal((int) $total);
            }
        } elseif ('{}CandidatsMaj' === $cityAttr['value'][9]['value'][0]['value'][7]['name']) {
            foreach ($this->getValueData('ListeCandidatsMaj', $cityAttr['value'][9]['value'][0]['value'][7]['value'])['value'] as $candidate) {
                $candidateFirstName = $this->getValueData('PrePsn', $candidate['value'])['value'];
                $candidateLastName = $this->getValueData('NomPsn', $candidate['value'])['value'];
                $listLabel = $candidateLastName.' '.$candidateFirstName;

                $total = $this->getValueData('NbVoix', $candidate['value'])['value'];

                $listToUpdate = $voteResult->findListWithLabel($listLabel);
                if (!$listToUpdate) {
                    $listToUpdate = new MinistryListTotalResult();
                    $listToUpdate->setLabel($listLabel);
                    $voteResult->addListTotalResult($listToUpdate);
                }

                $listToUpdate->setTotal((int) $total);
            }
        }

        if (!$voteResult->getId()) {
            $this->entityManager->persist($voteResult);
        }

        $voteResult->setUpdatedAt(new \DateTime());
        $voteResult->setUpdatedBy($this->author);

        $this->entityManager->flush();
    }

    private function getValueData(string $key, $rows): ?array
    {
        foreach ($rows as $row) {
            if ($row['name'] === '{}'.$key) {
                return $row;
            }
        }

        return null;
    }
}
