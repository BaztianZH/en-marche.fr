<?php

namespace App\VotingPlatform\VoteResult;

use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Repository\VotingPlatform\VoterRepository;

class VoteResultAggregator
{
    private $voteResultRepository;
    private $voterRepository;

    public function __construct(VoteResultRepository $voteResultRepository, VoterRepository $voterRepository)
    {
        $this->voteResultRepository = $voteResultRepository;
        $this->voterRepository = $voterRepository;
    }

    public function getResults(Election $election): array
    {
        $voteResults = $this->voteResultRepository->getResults($election);

        $aggregated = [
            'candidates' => [],
            'resume' => [],
        ];

        $participants = $this->voterRepository->countForElection($election);

        foreach ($voteResults as $voteResult) {
            foreach ($voteResult->getVoteChoices() as $index => $voteChoice) {
                if (!isset($aggregated['resume'][$index])) {
                    $aggregated['resume'][$index] = [
                        'blank' => 0,
                        'participated' => $participants,
                        'expressed' => 0,
                        'abstentions' => $participants - \count($voteResults),
                    ];
                }

                if (true === $voteChoice->isBlank()) {
                    ++$aggregated['resume'][$index]['blank'];
                } else {
                    ++$aggregated['resume'][$index]['expressed'];

                    $candidateGroupUuid = $voteChoice->getCandidateGroup()->getUuid()->toString();

                    if (!isset($aggregated['candidates'][$candidateGroupUuid])) {
                        $aggregated['candidates'][$candidateGroupUuid] = 0;
                    }

                    ++$aggregated['candidates'][$candidateGroupUuid];
                }
            }
        }

        // Sort candidates list
        arsort($aggregated['candidates']);

        return [
            'vote_results' => $voteResults,
            'aggregated' => $aggregated,
        ];
    }
}
