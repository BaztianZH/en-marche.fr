<?php

namespace AppBundle\Donation;

use Symfony\Component\Validator\Constraints as Assert;

class DonatorExtractCommand
{
    public const FIELD_EMAIL = 'email';
    public const FIELD_FIRST_NAME = 'firstName';
    public const FIELD_LAST_NAME = 'lastName';
    public const FIELD_GENDER = 'gender';
    public const FIELD_NATIONALITY = 'nationality';
    public const FIELD_PHONE = 'phone';
    public const FIELD_REGISTERED_AT = 'registeredAt';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_POSTAL_CODE = 'postalCode';
    public const FIELD_ADDRESS = 'address';

    public const FIELD_CHOICES = [
        self::FIELD_FIRST_NAME,
        self::FIELD_LAST_NAME,
        self::FIELD_GENDER,
        self::FIELD_NATIONALITY,
        self::FIELD_PHONE,
        self::FIELD_REGISTERED_AT,
        self::FIELD_COUNTRY,
        self::FIELD_POSTAL_CODE,
        self::FIELD_ADDRESS,
    ];

    /**
     * @var string[]|array
     *
     * @Assert\NotBlank
     * @Assert\All({
     *     @Assert\Email(message="{{ value }} n'est pas une adresse mail valide.")
     * })
     */
    private $emails = [];

    /**
     * @var string[]|array
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=DonatorExtractCommand::FIELD_CHOICES, multiple=true)
     */
    private $fields = [];

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }
}
