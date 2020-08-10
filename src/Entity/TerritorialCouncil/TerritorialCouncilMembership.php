<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository")
 *
 * @UniqueEntity(fields={"adherent", "territorialCouncil"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilMembership
{
    use EntityIdentityTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent", inversedBy="territorialCouncilMembership")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", unique=true)
     */
    private $adherent;

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil", inversedBy="memberships", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @var Collection|TerritorialCouncilQuality[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilQuality",
     *     cascade={"all"},
     *     mappedBy="territorialCouncilMembership",
     *     orphanRemoval=true
     * )
     */
    private $qualities;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    /**
     * @var Candidacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\Candidacy", mappedBy="membership")
     */
    private $candidacies;

    public function __construct(
        TerritorialCouncil $territorialCouncil = null,
        Adherent $adherent = null,
        \DateTime $joinedAt = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->territorialCouncil = $territorialCouncil;
        $this->adherent = $adherent;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');

        $this->qualities = new ArrayCollection();
        $this->candidacies = new ArrayCollection();
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    /**
     * @return Collection|TerritorialCouncilQuality[]
     */
    public function getQualities(): Collection
    {
        return $this->qualities;
    }

    public function addQuality(TerritorialCouncilQuality $quality): void
    {
        if (!$this->hasQuality($quality->getName())) {
            $quality->setTerritorialCouncilMembership($this);
            $this->qualities->add($quality);
        }
    }

    public function removeQuality(TerritorialCouncilQuality $quality): void
    {
        $this->qualities->removeElement($quality);
    }

    public function clearQualities(): void
    {
        $this->qualities->clear();
    }

    public function hasQuality(string $name): bool
    {
        return $this->getQualities()->filter(function (TerritorialCouncilQuality $quality) use ($name) {
            return $quality->getName() === $name;
        })->count() > 0;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function revoke(): void
    {
        $this->adherent = null;
    }

    public function getCandidacyForElection(Election $election): ?Candidacy
    {
        foreach ($this->candidacies as $candidacy) {
            if ($candidacy->getElection() === $election) {
                return $candidacy;
            }
        }

        return null;
    }

    public function getQualityNames(): array
    {
        return array_map(function (TerritorialCouncilQuality $quality) {
            return $quality->getName();
        }, $this->qualities->toArray());
    }

    public function isAvailableForCandidacy(Election $election = null): bool
    {
        if (!$election) {
            $election = $this->getTerritorialCouncil()->getCurrentElection();
        }

        if (array_intersect(TerritorialCouncilQualityEnum::HIGHEST_QUALITIES, $this->getQualityNames())) {
            return false;
        }

        $candidacy = $this->getCandidacyForElection($election);

        return !$candidacy || $candidacy->isDraft();
    }
}
