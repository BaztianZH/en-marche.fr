<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findByUuid(string $uuid): ?Election
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function hasElectionForCommittee(Committee $committee, Designation $designation): bool
    {
        return (bool) $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->innerJoin('e.electionEntity', 'ee')
            ->where('ee.committee = :committee AND e.designation = :designation')
            ->setParameters([
                'committee' => $committee,
                'designation' => $designation,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
