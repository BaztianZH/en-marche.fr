<?php

namespace App\VotingPlatform\Election\VoteCommand;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Election\VoteCommandStateEnum;

class VoteCommand
{
    /**
     * @var string
     */
    private $state = VoteCommandStateEnum::INITIALIZE;

    /**
     * @var CandidateGroup[]
     */
    private $candidateGroups = [];

    /**
     * @var string
     */
    private $electionUuid;

    public function __construct(Election $election)
    {
        $this->electionUuid = $election->getUuid()->toString();
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return CandidateGroup[]
     */
    public function getCandidateGroups(): array
    {
        return $this->candidateGroups;
    }

    public function isStart(): bool
    {
        return VoteCommandStateEnum::START === $this->state;
    }

    public function isVote(): bool
    {
        return VoteCommandStateEnum::VOTE === $this->state;
    }

    public function isConfirm(): bool
    {
        return VoteCommandStateEnum::CONFIRM === $this->state;
    }

    public function isFinish(): bool
    {
        return VoteCommandStateEnum::FINISH === $this->state;
    }

    public function getElectionUuid(): string
    {
        return $this->electionUuid;
    }

    public function getCandidateGroupUuids(): array
    {
        return array_filter($this->getCandidateGroups(), static function (string $value) {
            return VoteChoice::BLANK_VOTE_VALUE !== $value;
        });
    }
}
