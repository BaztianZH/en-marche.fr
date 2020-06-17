<?php

namespace App\Controller\EnMarche\EventManager;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent-delegue/{delegated_access_uuid}", name="app_referent_event_manager_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_EVENTS', request)")
 */
class DelegatedReferentEventManagerController extends ReferentEventManagerController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;
}
