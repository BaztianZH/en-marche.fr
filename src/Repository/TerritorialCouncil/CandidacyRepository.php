<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CandidacyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Candidacy::class);
    }

    /**
     * @return Candidacy[]
     */
    public function findAllConfirmedForElection(Election $election): array
    {
        return $this->createQueryBuilder('candidacy')
            ->addSelect('binome')
            ->leftJoin('candidacy.binome', 'binome')
            ->where('candidacy.election = :election')
            ->andWhere('candidacy.status = :confirmed')
            ->setParameters([
                'election' => $election,
                'confirmed' => Candidacy::STATUS_CONFIRMED,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCandidatesStats(Election $election): array
    {
        return $this->createQueryBuilder('candidacy')
            ->select('candidacy.quality')
            ->addSelect('COUNT(1) as total')
            ->where('candidacy.election = :election')
            ->andWhere('candidacy.status = :confirmed')
            ->groupBy('candidacy.quality')
            ->setParameters([
                'election' => $election,
                'confirmed' => Candidacy::STATUS_CONFIRMED,
            ])
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
