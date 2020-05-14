<?php

namespace App\DataFixtures\ORM;

use App\Election\VoteListNuanceEnum;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\LaREMSupportEnum;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\ElectedRepresentative\SocialLinkTypeEnum;
use App\Entity\ElectedRepresentative\SocialNetworkLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumber;

class LoadElectedRepresentativeData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // with adherent, mandate 92 CITY_COUNCIL : functions OTHER_MEMBER, PRESIDENT_OF_EPCI
        $erAdherent92 = new ElectedRepresentative('Michelle', 'DUFOUR', 'female', new \DateTime('1972-11-23'), 1203084);
        $erAdherent92->setAdherent($this->getReference('adherent-5'));
        $erAdherent92->setIsAdherent(true);
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::LAREM,
            $erAdherent92,
            true,
            '2017'
        );
        $mandate = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::REM,
            LaREMSupportEnum::OFFICIAL,
            $this->getReference('zone-epci-92-1'),
            $erAdherent92,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            'Some precisions',
            $erAdherent92,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erAdherent92,
            $mandate,
            false,
            new \DateTime('2015-03-24')
        );
        $erAdherent92->addLabel($label);
        $erAdherent92->addMandate($mandate);
        $erAdherent92->addPoliticalFunction($politicalFunction1);
        $erAdherent92->addPoliticalFunction($politicalFunction2);

        $manager->persist($erAdherent92);

        // with mandate 92 CITY_COUNCIL : functions MAYOR, PRESIDENT_OF_EPCI (finished)
        $erCityCouncilWithFinishedFunction = new ElectedRepresentative('Delphine', 'BOUILLOUX', 'female', new \DateTime('1977-08-02'), 1203080);
        $this->setPhoneNumber($erCityCouncilWithFinishedFunction, '0999887766');
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $erCityCouncilWithFinishedFunction,
            true,
            '2016'
        );
        $mandate = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::NC,
            LaREMSupportEnum::OFFICIAL,
            $this->getReference('zone-city-92110'),
            $erCityCouncilWithFinishedFunction,
            true,
            new \DateTime('2014-03-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            true,
            new \DateTime('2019-07-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $erCityCouncilWithFinishedFunction,
            $mandate,
            false,
            new \DateTime('2019-06-02'),
            new \DateTime('2016-01-06')
        );
        $twitter = new SocialNetworkLink('https://twitter.com/DeBou', SocialLinkTypeEnum::TWITTER, $erCityCouncilWithFinishedFunction);
        $instagram = new SocialNetworkLink('https://instagram.com/deBou', SocialLinkTypeEnum::INSTAGRAM, $erCityCouncilWithFinishedFunction);
        $telegram = new SocialNetworkLink('https://telegram.com/deBou', SocialLinkTypeEnum::TELEGRAM, $erCityCouncilWithFinishedFunction);
        $facecbook = new SocialNetworkLink('https://facebook.com/deBou', SocialLinkTypeEnum::FACEBOOK, $erCityCouncilWithFinishedFunction);
        $youtube = new SocialNetworkLink('https://youtube.com/deBou', SocialLinkTypeEnum::YOUTUBE, $erCityCouncilWithFinishedFunction);
        $erCityCouncilWithFinishedFunction->addLabel($label);
        $erCityCouncilWithFinishedFunction->addMandate($mandate);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction1);
        $erCityCouncilWithFinishedFunction->addPoliticalFunction($politicalFunction2);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($twitter);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($instagram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($telegram);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($facecbook);
        $erCityCouncilWithFinishedFunction->addSocialNetworkLink($youtube);

        $manager->persist($erCityCouncilWithFinishedFunction);

        // with mandate 76 CITY_COUNCIL : functions DEPUTY_MAYOR
        // with mandate 76 EPCI_MEMBER : functions PRESIDENT_OF_EPCI
        $er2Mandates = new ElectedRepresentative('Daniel', 'BOULON', 'male', new \DateTime('1951-03-04'), 694516);
        $er2Mandates->setIsAdherent(null);
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::PS,
            $er2Mandates,
            false,
            '2014',
            '2018'
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $er2Mandates,
            true,
            '2018'
        );
        $mandate1 = new Mandate(
            MandateTypeEnum::CITY_COUNCIL,
            true,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            $this->getReference('zone-city-76000'),
            $er2Mandates,
            true,
            new \DateTime('2014-03-23')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::DEPUTY_MAYOR,
            null,
            $er2Mandates,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            MandateTypeEnum::EPCI_MEMBER,
            true,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            $this->getReference('zone-city-76000'),
            $er2Mandates,
            true,
            new \DateTime('2017-01-11')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::PRESIDENT_OF_EPCI,
            null,
            $er2Mandates,
            $mandate2,
            false,
            new \DateTime('2019-07-23 ')
        );
        $er2Mandates->addLabel($label1);
        $er2Mandates->addLabel($label2);
        $er2Mandates->addMandate($mandate1);
        $er2Mandates->addMandate($mandate2);
        $er2Mandates->addPoliticalFunction($politicalFunction1);
        $er2Mandates->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2Mandates);

        // with mandate 94 SENATOR, no function
        // with mandate 76 DEPUTY : functions OTHER_MEMBER
        $er2MandatesOneFinished = new ElectedRepresentative('Roger', 'BUET', 'male', new \DateTime('1952-04-21'), 873399);
        $label = new ElectedRepresentativeLabel(
            LabelNameEnum::OTHER,
            $er2MandatesOneFinished,
            true,
            '2014'
        );
        $mandate1 = new Mandate(
            MandateTypeEnum::SENATOR,
            true,
            VoteListNuanceEnum::FN,
            LaREMSupportEnum::INFORMAL,
            $this->getReference('zone-region-94'),
            $er2MandatesOneFinished,
            true,
            new \DateTime('2016-03-23')
        );
        $mandate2 = new Mandate(
            MandateTypeEnum::DEPUTY,
            true,
            VoteListNuanceEnum::FN,
            LaREMSupportEnum::NOT_SUPPORTED,
            $this->getReference('zone-city-76000'),
            $er2MandatesOneFinished,
            true,
            new \DateTime('2011-12-23'),
            new \DateTime('2015-02-23')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::OTHER_MEMBER,
            null,
            $er2MandatesOneFinished,
            $mandate2,
            false,
            new \DateTime('2019-07-23 ')
        );
        $er2MandatesOneFinished->addLabel($label);
        $er2MandatesOneFinished->addMandate($mandate1);
        $er2MandatesOneFinished->addMandate($mandate2);
        $er2MandatesOneFinished->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2MandatesOneFinished);

        // with mandate EURO_DEPUTY, no function
        $erEuroDeputy2Labels = new ElectedRepresentative('Sans', 'OFFICIELID', 'male', new \DateTime('1951-11-03'), 873404);
        $label1 = new ElectedRepresentativeLabel(
            LabelNameEnum::MRC,
            $erEuroDeputy2Labels,
            false,
            '2014',
            '2017'
        );
        $label2 = new ElectedRepresentativeLabel(
            LabelNameEnum::GS,
            $erEuroDeputy2Labels,
            true,
            '2017'
        );
        $mandate = new Mandate(
            MandateTypeEnum::EURO_DEPUTY,
            true,
            VoteListNuanceEnum::ALLI,
            LaREMSupportEnum::INVESTED,
            null,
            $erEuroDeputy2Labels,
            true,
            new \DateTime('2016-03-23')
        );
        $erEuroDeputy2Labels->addLabel($label1);
        $erEuroDeputy2Labels->addLabel($label2);
        $erEuroDeputy2Labels->addMandate($mandate1);

        $manager->persist($erEuroDeputy2Labels);

        // with mandate 13 DEPUTY : functions VICE_PRESIDENT_OF_EPCI
        // with mandate 13 REGIONAL_COUNCIL : functions PRESIDENT_OF_EPCI
        $er2Mandates2Functions = new ElectedRepresentative('André', 'LOBELL', 'male', new \DateTime('1951-11-03'), 873404);
        $mandate1 = new Mandate(
            MandateTypeEnum::DEPUTY,
            true,
            VoteListNuanceEnum::RN,
            LaREMSupportEnum::INFORMAL,
            $this->getReference('zone-dpt-13'),
            $er2Mandates2Functions,
            true,
            new \DateTime('2015-03-13')
        );
        $politicalFunction1 = new PoliticalFunction(
            PoliticalFunctionNameEnum::VICE_PRESIDENT_OF_EPCI,
            null,
            $er2Mandates2Functions,
            $mandate1,
            true,
            new \DateTime('2014-03-23')
        );
        $mandate2 = new Mandate(
            MandateTypeEnum::REGIONAL_COUNCIL,
            true,
            VoteListNuanceEnum::DIV,
            LaREMSupportEnum::NOT_SUPPORTED,
            $this->getReference('zone-city-76000'),
            $er2Mandates2Functions,
            true,
            new \DateTime('2017-07-18')
        );
        $politicalFunction2 = new PoliticalFunction(
            PoliticalFunctionNameEnum::MAYOR_ASSISTANT,
            null,
            $er2Mandates2Functions,
            $mandate2,
            false,
            new \DateTime('2019-05-10 ')
        );
        $er2Mandates2Functions->addMandate($mandate1);
        $er2Mandates2Functions->addMandate($mandate2);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction1);
        $er2Mandates2Functions->addPoliticalFunction($politicalFunction2);

        $manager->persist($er2Mandates2Functions);

        // with not elected mandate Corsica CORSICA_ASSEMBLY_MEMBER
        $erWithNotElectedMandate = new ElectedRepresentative('Jesuis', 'PASELU', 'male', new \DateTime('1981-01-03'));
        $mandate = new Mandate(
            MandateTypeEnum::CORSICA_ASSEMBLY_MEMBER,
            false,
            VoteListNuanceEnum::ALLI,
            LaREMSupportEnum::INFORMAL,
            $this->getReference('zone-corsica'),
            $erWithNotElectedMandate,
            false,
            new \DateTime('2020-03-15')
        );
        $erWithNotElectedMandate->addMandate($mandate);

        $manager->persist($erWithNotElectedMandate);

        $manager->flush();
    }

    private function setPhoneNumber(ElectedRepresentative $er, string $phoneNumber): void
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('33');
        $phone->setNationalNumber($phoneNumber);
        $er->setContactPhone($phone);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadZoneData::class,
        ];
    }
}
