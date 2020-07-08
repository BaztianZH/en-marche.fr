<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeDeleteCommand;
use App\Utils\ArrayUtils;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ElectedRepresentativeEventSubscriber implements EventSubscriberInterface
{
    private $normalizer;
    private $bus;
    private $beforeUpdate;

    public function __construct(ArrayTransformerInterface $normalizer, MessageBusInterface $bus)
    {
        $this->normalizer = $normalizer;
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            ElectedRepresentativeEvents::BEFORE_UPDATE => 'onBeforeUpdate',
            ElectedRepresentativeEvents::POST_UPDATE => 'postUpdate',
        ];
    }

    public function onBeforeUpdate(ElectedRepresentativeEvent $event): void
    {
        $this->beforeUpdate = $this->transformToArray($event->getElectedRepresentative());
    }

    public function postUpdate(ElectedRepresentativeEvent $event): void
    {
        $electedRepresentative = $event->getElectedRepresentative();
        $emailBeforeUpdate = isset($this->beforeUpdate['emailAddress']) ? $this->beforeUpdate['emailAddress'] : null;

        if (!$electedRepresentative->getEmailAddress() && $emailBeforeUpdate) {
            $this->bus->dispatch(new ElectedRepresentativeDeleteCommand($emailBeforeUpdate));

            return;
        }

        $afterUpdate = $this->transformToArray($electedRepresentative);

        $changeFrom = ArrayUtils::arrayDiffRecursive($this->beforeUpdate, $afterUpdate);
        $changeTo = ArrayUtils::arrayDiffRecursive($afterUpdate, $this->beforeUpdate);

        if ($changeFrom || $changeTo) {
            $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
                $electedRepresentative->getUuid(),
                $emailBeforeUpdate ?? $electedRepresentative->getEmailAddress(),
                isset($changeFrom['activeTagCodes']) ? (array) $changeFrom['activeTagCodes'] : []
            ));
        }
    }

    private function transformToArray(ElectedRepresentative $electedRepresentative): array
    {
        return $this->normalizer->toArray(
            $electedRepresentative,
            SerializationContext::create()->setGroups(['elected_representative_change_diff'])
        );
    }
}
