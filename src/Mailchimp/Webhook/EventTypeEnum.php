<?php

namespace AppBundle\Mailchimp\Webhook;

use MyCLabs\Enum\Enum;

class EventTypeEnum extends Enum
{
    public const UPDATE_EMAIL = 'upemail';
}
