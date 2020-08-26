<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TerritorialCouncilDesignation extends Constraint
{
    public $messageVoteStartDateTooClose = 'La convocation doit avoir lieu au minimum 7 jours avant la tenue du vote.';
    public $messageVoteEndDateInvalid = 'La durée du vote doit être comprise entre 5h et 7 jours.';
    public $messageMeetingDateInvalid = 'La date de la réunion doit se situer entre les dates du vote.';
    public $messageMeetingStartDateInvalid = 'La date de début de la réunion doit être la même que la date de début du vote.';
    public $messageMeetingEndDateInvalid = 'La durée de la réunion ne doit pas dépasser 12 heures.';
    public $messageElectionPollChoiceInvalid = 'Les choix du sondage sont invalides.';
    public $messageAddressEmpty = 'L\'adresse ne doit pas être vide.';
    public $messageUrlEmpty = 'L\'URL de la réunion ne doit pas être vide.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
