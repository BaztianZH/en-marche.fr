<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur-delegue/messagerie", name="app_message_senator_delegated_")
 *
 * @Security("is_granted('IS_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES', 'senator')")
 */
class DelegatedSenatorMessageController extends SenatorMessageController
{
    use AccessDelegatorTrait;

    protected function redirectToMessageRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_message_{$this->getMessageType()}_delegated_${subName}", $parameters);
    }
}
