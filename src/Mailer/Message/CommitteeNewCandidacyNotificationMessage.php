<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeCandidacy;
use AppBundle\Entity\CommitteeElection;
use Ramsey\Uuid\Uuid;

final class CommitteeNewCandidacyNotificationMessage extends Message
{
    public static function create(
        CommitteeCandidacy $committeeCandidacy,
        CommitteeElection $election,
        Adherent $supervisor,
        Adherent $candidate,
        string $committeeUrl
    ): self {
        return new self(
            Uuid::uuid4(),
            $supervisor->getEmailAddress(),
            $supervisor->getFullName(),
            '[Désignations] Une nouvelle candidature a été déposée',
            static::getTemplateVars($committeeCandidacy, $election, $supervisor, $candidate, $committeeUrl),
        );
    }

    private static function getTemplateVars(
        CommitteeCandidacy $committeeCandidacy,
        CommitteeElection $election,
        Adherent $supervisor,
        Adherent $candidate,
        string $committeeUrl
    ): array {
        return [
            'supervisor_first_name' => $supervisor->getFirstName(),
            'candidate_civility' => $committeeCandidacy->getCivility(),
            'candidate_first_name' => $candidate->getFirstName(),
            'candidate_last_name' => $candidate->getLastName(),
            'vote_start_date' => self::dateToString($election->getVoteStartDate()),
            'vote_end_date' => self::dateToString($election->getVoteEndDate()),
            'committee_url' => $committeeUrl,
        ];
    }

    private static function dateToString(\DateTimeInterface $date): string
    {
        return parent::formatDate($date, 'EEEE d MMMM y, HH\'h\'mm');
    }
}
