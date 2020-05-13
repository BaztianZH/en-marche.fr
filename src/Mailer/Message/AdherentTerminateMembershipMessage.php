<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentTerminateMembershipMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre départ d\'En Marche !',
            [],
            static::getRecipientVars($adherent->getFirstName())
        );
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
