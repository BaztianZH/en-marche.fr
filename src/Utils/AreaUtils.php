<?php

namespace App\Utils;

use App\Entity\District;
use App\Entity\EntityPostAddressInterface;
use App\Intl\FranceCitiesBundle;

class AreaUtils
{
    public const CODE_CORSICA_A = '2A';
    public const CODE_CORSICA_B = '2B';
    public const CODE_FRANCE = 'FR';
    public const CODE_MONACO = 'MC';
    public const CODE_SAINT_BARTHELEMY = '97133';
    public const CODE_SAINT_MARTIN = '97150';
    public const POSTALCODE_MONACO = '98000';
    public const PREFIX_POSTALCODE_CORSICA = '20';
    public const PREFIX_POSTALCODE_CORSICA_A = ['200', '201'];
    public const PREFIX_POSTALCODE_DOM = '97';
    public const PREFIX_POSTALCODE_PARIS_DISTRICTS = '75';
    public const PREFIX_POSTALCODE_TOM = '98';
    public const DISTRICT_PARIS = [
        '75001' => ['75001', '75002', '75008', '75009'],
        '75002' => ['75005', '75006', '75007'],
        '75003' => ['75017', '75018'],
        '75004' => ['75016', '75017'],
        '75005' => ['75003', '75010'],
        '75006' => ['75011', '75020'],
        '75007' => ['75004', '75011', '75012'],
        '75008' => ['75012', '75020'],
        '75009' => ['75013'],
        '75010' => ['75013', '75014'],
        '75011' => ['75006', '75014'],
        '75012' => ['75007', '75015'],
        '75013' => ['75015'],
        '75014' => ['75016'],
        '75015' => ['75020'],
        '75016' => ['75019'],
        '75017' => ['75018', '75019'],
        '75018' => ['75009', '75018'],
    ];

    public const INSEE_CODE_ANNECY = '74010';
    public const INSEE_CODES_ATTACHED_TO_ANNECY = [
        '74011',
        '74268',
        '74093',
        '74182',
        '74217',
    ];

    public const METROPOLIS = [
        '34M' => [
            '34022',
            '34027',
            '34057',
            '34058',
            '34077',
            '34087',
            '34088',
            '34090',
            '34095',
            '34116',
            '34120',
            '34123',
            '34129',
            '34134',
            '34164',
            '34169',
            '34172',
            '34179',
            '34198',
            '34202',
            '34217',
            '34227',
            '34244',
            '34249',
            '34256',
            '34259',
            '34270',
            '34295',
            '34307',
            '34327',
            '34337',
        ],
        '69M' => [
            '69003',
            '69029',
            '69033',
            '69034',
            '69040',
            '69044',
            '69046',
            '69271',
            '69063',
            '69273',
            '69068',
            '69069',
            '69071',
            '69072',
            '69275',
            '69081',
            '69276',
            '69085',
            '69087',
            '69088',
            '69089',
            '69278',
            '69091',
            '69096',
            '69100',
            '69279',
            '69116',
            '69117',
            '69123',
            '69127',
            '69282',
            '69283',
            '69284',
            '69142',
            '69143',
            '69149',
            '69152',
            '69153',
            '69163',
            '69286',
            '69168',
            '69191',
            '69194',
            '69199',
            '69204',
            '69205',
            '69207',
            '69290',
            '69233',
            '69202',
            '69292',
            '69293',
            '69296',
            '69244',
            '69250',
            '69256',
            '69259',
            '69260',
            '69266',
            // Lyon district INSEE codes
            '69381',
            '69382',
            '69383',
            '69384',
            '69385',
            '69386',
            '69387',
            '69388',
            '69389',
        ],
    ];

    public static function getCodeFromPostalCode(?string $postalCode): ?string
    {
        $department = mb_substr($postalCode, 0, 2);

        switch ($department) {
            case self::PREFIX_POSTALCODE_PARIS_DISTRICTS:
                return $postalCode;
            case self::PREFIX_POSTALCODE_TOM:
                return self::POSTALCODE_MONACO === $postalCode ? self::CODE_MONACO : mb_substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_DOM:
                if (\in_array($postalCode, [self::CODE_SAINT_BARTHELEMY, self::CODE_SAINT_MARTIN], true)) {
                    return $postalCode;
                }

                return mb_substr($postalCode, 0, 3);
            case self::PREFIX_POSTALCODE_CORSICA:
                return \in_array(mb_substr($postalCode, 0, 3), self::PREFIX_POSTALCODE_CORSICA_A, true)
                    ? self::CODE_CORSICA_A
                    : self::CODE_CORSICA_B
                ;
            default:
                return $department;
        }
    }

    public static function getCodeFromCountry(string $country): string
    {
        if (!\in_array($country, FranceCitiesBundle::$countries, true)) {
            return $country;
        }

        $code = (string) array_search($country, FranceCitiesBundle::$countries);

        return self::POSTALCODE_MONACO === $code ? self::CODE_MONACO : $code;
    }

    public static function getCodeFromDistrict(District $district): array
    {
        if ($district->isFrenchDistrict()) {
            $code = $district->getDepartmentCode();
            if (self::PREFIX_POSTALCODE_PARIS_DISTRICTS === $code) {
                return array_merge(['FR', '75'], self::DISTRICT_PARIS[$district->getCode()]);
            } else {
                return ['FR', $district->getDepartmentCode()];
            }
        } else {
            foreach ($district->getCountries() as $country) {
                $codes[] = self::getCodeFromCountry($country);
            }

            return $codes;
        }
    }

    public static function getMetropolisCode(EntityPostAddressInterface $entity): ?string
    {
        $metropolisCode = null;

        if (self::CODE_FRANCE === $entity->getCountry()) {
            foreach (self::METROPOLIS as $codeM => $codes) {
                $metropolisCode = \in_array($entity->getInseeCode(), $codes) ? $codeM : null;

                if ($metropolisCode) {
                    break;
                }
            }
        }

        return $metropolisCode;
    }

    public static function getRelatedCodes(string $code): array
    {
        $relatedCodes = [];

        if (static::isParisCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_PARIS_DISTRICTS;
        }

        if (static::isCorsicaCode($code)) {
            $relatedCodes[] = self::PREFIX_POSTALCODE_CORSICA;
        }

        return $relatedCodes;
    }

    private static function isParisCode(string $code): bool
    {
        return self::PREFIX_POSTALCODE_PARIS_DISTRICTS === mb_substr($code, 0, 2);
    }

    private static function isCorsicaCode(string $code): bool
    {
        return \in_array($code, [self::CODE_CORSICA_A, self::CODE_CORSICA_B], true);
    }
}
