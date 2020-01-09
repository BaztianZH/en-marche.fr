<?php

namespace AppBundle\Repository\Projection;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator as ApiPaginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\ManagedUsers\ManagedUsersFilter;
use AppBundle\Repository\ReferentTagRepository;
use AppBundle\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentManagedUserRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentManagedUser::class);
    }

    public function searchByFilter(ManagedUsersFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return new ApiPaginator(new Paginator($this
            ->createFilterQueryBuilder($filter)
            ->setFirstResult((($page < 1 ? 1 : $page) - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->useResultCache(true)
            ->setResultCacheLifetime(1800)
        ));
    }

    private function createFilterQueryBuilder(ManagedUsersFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.status = :status')
            ->setParameter('status', ReferentManagedUser::STATUS_READY)
            ->orderBy('u.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        $this->withZoneCondition($qb, $filter->getReferentTags());

        if ($queryAreaCode = $filter->getCityAsArray()) {
            $areaCodeExpression = $qb->expr()->orX();

            foreach ($queryAreaCode as $key => $areaCode) {
                if (is_numeric($areaCode)) {
                    $areaCodeExpression->add('u.postalCode LIKE :postalCode_'.$key.' OR u.committeePostalCode LIKE :postalCode_'.$key);
                    $qb->setParameter('postalCode_'.$key, $areaCode.'%');
                }

                if (\is_string($areaCode)) {
                    $areaCodeExpression->add('u.country = :countryOrCity_'.$key.' OR u.city = :countryOrCity_'.$key);
                    $qb->setParameter('countryOrCity_'.$key, $areaCode);
                }
            }

            $qb->andWhere($areaCodeExpression);
        }

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('u.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('u.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($ageMin = $filter->getAgeMin()) {
            $qb
                ->andWhere('u.age >= :age_min')
                ->setParameter('age_min', $ageMin)
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $qb
                ->andWhere('u.age <= :age_max')
                ->setParameter('age_max', $ageMax)
            ;
        }

        if ($registeredSince = $filter->getRegisteredSince()) {
            $qb
                ->andWhere('u.createdAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $qb
                ->andWhere('u.createdAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        foreach (array_values($filter->getInterests()) as $key => $interest) {
            $qb
                ->andWhere(sprintf('FIND_IN_SET(:interest_%s, u.interests) > 0', $key))
                ->setParameter('interest_'.$key, $interest)
            ;
        }

        $typeExpression = $qb->expr()->orX();

        if ($filter->includeAdherentsNoCommittee()) {
            $typeExpression->add('u.type = :type_anc AND u.isCommitteeMember = 0');
            $qb->setParameter('type_anc', ReferentManagedUser::TYPE_ADHERENT);
        }

        if ($filter->includeAdherentsInCommittee()) {
            $typeExpression->add('u.type = :type_aic AND u.isCommitteeMember = 1');
            $qb->setParameter('type_aic', ReferentManagedUser::TYPE_ADHERENT);
        }

        if ($filter->includeCommitteeHosts()) {
            $typeExpression->add('u.type = :type_h AND u.isCommitteeHost = 1');
            $qb->setParameter('type_h', ReferentManagedUser::TYPE_ADHERENT);
        }

        if ($filter->includeCommitteeSupervisors()) {
            $and = new Andx();
            $and->add('u.type = :type_s AND u.isCommitteeSupervisor = 1');
            $qb->setParameter('type_s', ReferentManagedUser::TYPE_ADHERENT);

            $supervisorExpression = $qb->expr()->orX();
            foreach ($filter->getReferentTags() as $key => $code) {
                $supervisorExpression->add(sprintf('FIND_IN_SET(:code_%s, u.supervisorTags) > 0', $key));
                $qb->setParameter('code_'.$key, $code->getCode());
            }

            $and->add($supervisorExpression);
            $typeExpression->add($and);
        }

        if ($filter->includeCitizenProjectHosts()) {
            $typeExpression->add('json_length(u.citizenProjectsOrganizer) > 0');
        }

        $qb->andWhere($typeExpression);

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $qb
                ->andWhere(sprintf('FIND_IN_SET(:subscription_type, u.subscriptionTypes) %s 0', $filter->getEmailSubscription() ? '>' : '='))
                ->setParameter('subscription_type', $filter->getSubscriptionType())
            ;
        }

        return $qb;
    }

    public function countManagedUsers(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
        ;

        return (int) $this
            ->withZoneCondition($qb, $referentTags)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function withZoneCondition(QueryBuilder $qb, array $referentTags, string $alias = 'u'): QueryBuilder
    {
        if (1 === \count($referentTags) && ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === ($tag = current($referentTags))->getCode()) {
            return $qb->andWhere("${alias}.country != 'FR'");
        }

        $tagsFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $tagsFilter->add("FIND_IN_SET(:tag_$key, $alias.subscribedTags) > 0");
            $tagsFilter->add(
                $qb->expr()->andX(
                    "$alias.country = 'FR'",
                    $qb->expr()->like("$alias.committeePostalCode", ":tag_prefix_$key")
                )
            );
            $qb->setParameter("tag_$key", $tag->getCode());
            $qb->setParameter("tag_prefix_$key", $tag->getCode().'%');
        }

        return $qb->andWhere($tagsFilter);
    }
}
