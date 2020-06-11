<?php

namespace App\Controller;

use App\Entity\MyTeam\DelegatedAccess;

trait AccessDelegatorTrait
{
    // if you need to retrieve the current user and not the delegator, use parent::getUser() in your contoller
    protected function getUser(string $type = null)
    {
        $this->disableInProduction();

        $user = parent::getUser();

        if (null === $type) {
            if (\method_exists($this, 'getSpaceType')) {
                $type = $this->getSpaceType();
            } elseif (\method_exists($this, 'getMessageType')) {
                $type = $this->getMessageType();
            } else {
                throw new \LogicException('Unable to determine space type');
            }
        }

        /** @var DelegatedAccess $delegatedAccess */
        $delegatedAccess = $this->get('request_stack')->getMasterRequest()->attributes->get('delegated_access');

        if (!$delegatedAccess || $delegatedAccess->getType() !== $type) {
            throw new \LogicException("Current user does not have a \"$type\" access");
        }

        return $delegatedAccess->getDelegator() ?? $user;
    }
}
