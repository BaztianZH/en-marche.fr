<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;

class ReferentToAdherentConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            && false === $filter->getContactOnlyVolunteers()
            && false === $filter->getContactOnlyRunningMates()
        ;
    }

    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        if (!$campaign->getStaticSegmentId()) {
            throw new StaticSegmentIdMissingException(sprintf('[ReferentMessage] Referent message (%s) does not have a Mailchimp Static segment ID', $campaign->getMessage()->getUuid()->toString()));
        }

        return $campaign->getStaticSegmentId();
    }
}
