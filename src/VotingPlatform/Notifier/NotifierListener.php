<?php

namespace App\VotingPlatform\Notifier;

use App\VotingPlatform\Events;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteIsOpenEvent;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteReminderEvent;
use App\VotingPlatform\Notifier\Event\ElectionNotifyEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifierListener implements EventSubscriberInterface
{
    private $notifier;

    public function __construct(ElectionNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::VOTE_OPEN => 'onVoteOpen',
            Events::CANDIDACY_PERIOD_CLOSE => 'onCandidacyPeriodClose',
            Events::VOTE_REMIND => 'onVoteRemind',
            Events::VOTE_CLOSE => 'onVoteClose',
        ];
    }

    public function onVoteOpen(ElectionNotifyEventInterface $event): void
    {
        if ($event instanceof CommitteeElectionVoteIsOpenEvent) {
            $this->notifier->notifyCommitteeElectionVoteIsOpen(
                $event->getAdherent(),
                $event->getDesignation(),
                $event->getCommittee()
            );
        }
    }

    public function onCandidacyPeriodClose(ElectionNotifyEventInterface $event): void
    {
        if ($event instanceof CommitteeElectionCandidacyPeriodIsOverEvent) {
            $this->notifier->notifyCommitteeElectionCandidacyPeriodIsOver(
                $event->getAdherent(),
                $event->getDesignation(),
                $event->getCommittee()
            );
        }
    }

    public function onVoteRemind(ElectionNotifyEventInterface $event): void
    {
        if ($event instanceof CommitteeElectionVoteReminderEvent) {
            $this->notifier->notifyCommitteeElectionVoteReminder(
                $event->getAdherent(),
                $event->getDesignation(),
                $event->getCommittee()
            );
        }
    }

    public function onVoteClose(ElectionNotifyEventInterface $event): void
    {
        if ($event instanceof CommitteeElectionVoteIsOverEvent) {
            $this->notifier->notifyCommitteeElectionVoteIsOver(
                $event->getAdherent(),
                $event->getCommittee()
            );
        }
    }
}
