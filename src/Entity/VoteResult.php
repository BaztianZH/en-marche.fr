<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteResultRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class VoteResult
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var VotePlace
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\VotePlace")
     * @ORM\JoinColumn(nullable=false)
     */
    private $votePlace;

    /**
     * @var ElectionRound
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ElectionRound")
     * @ORM\JoinColumn(nullable=false)
     */
    private $electionRound;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $registered = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $abstentions = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $voters = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $expressed = 0;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $lists = [];

    public function __construct(VotePlace $votePlace, ElectionRound $electionRound, Adherent $author)
    {
        $this->votePlace = $votePlace;
        $this->electionRound = $electionRound;
        $this->author = $author;

        $this->addList('', 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotePlace(): ?VotePlace
    {
        return $this->votePlace;
    }

    public function setVotePlace(VotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getElectionRound(): ?ElectionRound
    {
        return $this->electionRound;
    }

    public function setElectionRound(ElectionRound $electionRound): void
    {
        $this->electionRound = $electionRound;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getRegistered(): ?int
    {
        return $this->registered;
    }

    public function setRegistered(int $registered): void
    {
        $this->registered = $registered;
    }

    public function getAbstentions(): ?int
    {
        return $this->abstentions;
    }

    public function setAbstentions(int $abstentions): void
    {
        $this->abstentions = $abstentions;
    }

    public function getVoters(): ?int
    {
        return $this->voters;
    }

    public function setVoters(int $voters): void
    {
        $this->voters = $voters;
    }

    public function getExpressed(): ?int
    {
        return $this->expressed;
    }

    public function setExpressed(int $expressed): void
    {
        $this->expressed = $expressed;
    }

    public function getLists(): array
    {
        return $this->lists;
    }

    public function setLists(array $lists): void
    {
        $this->lists = $lists;
    }

    private function addList(string $label, int $votes): void
    {
        $this->lists[] = ['label' => $label, 'votes' => $votes];
    }
}
