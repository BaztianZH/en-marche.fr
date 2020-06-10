<?php

namespace App\ElectedRepresentative\Filter;

use App\Entity\ReferentTag;
use Symfony\Component\Validator\Constraints as Assert;

class ListFilter
{
    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $gender;

    /**
     * @var array|null
     */
    private $labels = [];

    /**
     * @var array|null
     */
    private $mandates = [];

    /**
     * @var array|null
     */
    private $politicalFunctions = [];

    /**
     * @var array|null
     */
    private $cities = [];

    /**
     * @var array|null
     */
    private $userListDefinitions = [];

    /**
     * @var bool|null
     */
    private $isAdherent;

    /**
     * @var ReferentTag[]
     *
     * @Assert\NotNull
     */
    private $referentTags = [];

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"lastName"})
     */
    private $sort = 'lastName';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'a';

    public function __construct(array $referentTags = [])
    {
        $this->referentTags = $referentTags;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function setLabels(?array $labels): void
    {
        $this->labels = $labels;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getPoliticalFunctions(): ?array
    {
        return $this->politicalFunctions;
    }

    public function setPoliticalFunctions(?array $politicalFunctions): void
    {
        $this->politicalFunctions = $politicalFunctions;
    }

    public function getCities(): ?array
    {
        return $this->cities;
    }

    public function setCities(?array $cities): void
    {
        $this->cities = $cities;
    }

    public function getUserListDefinitions(): ?array
    {
        return $this->userListDefinitions;
    }

    public function setUserListDefinitions(?array $userListDefinitions): void
    {
        $this->userListDefinitions = $userListDefinitions;
    }

    public function isAdherent(): ?bool
    {
        return $this->isAdherent;
    }

    public function setIsAdherent(?bool $isAdherent): void
    {
        $this->isAdherent = $isAdherent;
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags[] = $referentTag;
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        foreach ($this->referentTags as $key => $tag) {
            if ($tag->getId() === $referentTag->getId()) {
                unset($this->referentTags[$key]);
            }
        }
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function toArray(): array
    {
        return [
            'referentTags' => 1 === \count($this->referentTags) ? current($this->referentTags)->getId() : null,
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}
