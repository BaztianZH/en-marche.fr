<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OfficialReportRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OfficialReport::class);
    }

    /**
     * @return OfficialReport[]|PaginatorInterface
     */
    public function getPaginator(int $page = 1, int $limit = 30): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('report')
            ->orderBy('report.createdAt', 'DESC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }
}
