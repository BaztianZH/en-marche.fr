<?php

namespace AppBundle\ChezVous\Measure;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;

class CouvertureFibre extends AbstractMeasure
{
    public const TYPE = 'couverture_fibre';
    public const KEY_NOMBRE_LOCAUX_RACCORDES_VILLE = 'nombre_locaux_raccordes_ville';
    public const KEY_HAUSSE_DEPUIS_2017_VILLE = 'hausse_depuis_2017_ville';
    public const KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT = 'nombre_locaux_raccordes_departement';
    public const KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT = 'hausse_depuis_2017_departement';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function getKeys(): array
    {
        return [
            self::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE => true,
            self::KEY_HAUSSE_DEPUIS_2017_VILLE => true,
            self::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT => true,
            self::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT => true,
        ];
    }

    public static function create(
        City $city,
        int $nombreLocauxRaccordesVille,
        int $hausseDepuis2017Ville,
        int $nombreLocauxRaccordesDepartement,
        int $hausseDepuis2017Departement
    ): Measure {
        $measure = self::createMeasure($city);
        $measure->setPayload(self::createPayload(
            $nombreLocauxRaccordesVille,
            $hausseDepuis2017Ville,
            $nombreLocauxRaccordesDepartement,
            $hausseDepuis2017Departement
        ));

        return $measure;
    }

    public static function createPayload(
        int $nombreLocauxRaccordesVille,
        int $hausseDepuis2017Ville,
        int $nombreLocauxRaccordesDepartement,
        int $hausseDepuis2017Departement
    ): array {
        return [
            self::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE => $nombreLocauxRaccordesVille,
            self::KEY_HAUSSE_DEPUIS_2017_VILLE => $hausseDepuis2017Ville,
            self::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT => $nombreLocauxRaccordesDepartement,
            self::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT => $hausseDepuis2017Departement,
        ];
    }
}
