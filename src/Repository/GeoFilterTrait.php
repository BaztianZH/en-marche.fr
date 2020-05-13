<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use Doctrine\ORM\QueryBuilder;

trait GeoFilterTrait
{
    private function applyReferentGeoFilter(QueryBuilder $qb, Adherent $referent, string $alias): void
    {
        if (!$referent->isReferent()) {
            return;
        }

        $this->applyGeoFilter($qb, $referent->getManagedArea()->getTags()->toArray(), $alias);
    }

    /**
     * @param ReferentTag[] $referentTags
     */
    public function applyGeoFilter(
        QueryBuilder $qb,
        array $referentTags,
        string $alias,
        string $countryColumn = null,
        string $postalCodeColumn = null
    ): void {
        if (!$countryColumn) {
            $countryColumn = "$alias.postAddress.country";
        }

        if (!$postalCodeColumn) {
            $postalCodeColumn = "$alias.postAddress.postalCode";
        }

        $codesFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $code = $tag->getCode();

            if (is_numeric($code) || $tag->isDistrictTag()) {
                if ($tag->isDistrictTag()) {
                    $code = substr($code, 6, 2);
                }

                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        "${countryColumn} = 'FR'",
                        $qb->expr()->like("${postalCodeColumn}", ":code_$key")
                    )
                );

                $qb->setParameter("code_$key", "$code%");
            } elseif (2 === \mb_strlen($code)) {
                // Country
                $codesFilter->add($qb->expr()->eq("${countryColumn}", ":code_$key"));
                $qb->setParameter("code_$key", $code);
            } elseif (ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $code) {
                $codesFilter->add("${countryColumn} != 'FR'");
            }
        }

        $qb->andWhere($codesFilter);
    }
}
