<?php

namespace App\Entity\AdherentMandate;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilAdherentMandate extends AbstractAdherentMandate
{
    /**
     * @var TerritorialCouncil
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank(message="common.quality.invalid_choice")
     * @Assert\Choice(choices=App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS, strict=true)
     */
    private $quality;

    public function __construct(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil,
        string $quality,
        string $gender,
        \DateTime $beginAt,
        \DateTime $finishAt = null
    ) {
        parent::__construct($adherent, $gender, $beginAt, $finishAt);

        $this->territorialCouncil = $territorialCouncil;
        $this->quality = $quality;
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }
}
