<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use App\ManagedUsers\ManagedUsersFilter;
use App\Subscription\SubscriptionTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur-delegue/{delegated_access_uuid}", name="app_senator_managed_users_delegated_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS', request)")
 */
class DelegatedSenatorManagedUsersController extends SenatorManagedUsersController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function createFilterModel(Request $request): ManagedUsersFilter
    {
        $this->disableInProduction();

        /** @var DelegatedAccess $delegatedAccess */
        $delegatedAccess = $this->getDelegatedAccess($request);
        if (!$delegatedAccess) {
            throw new \LogicException('No delegated access found');
        }

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::SENATOR_EMAIL,
            [$delegatedAccess->getDelegator()->getSenatorArea()->getDepartmentTag()],
            $delegatedAccess->getRestrictedCommittees()->map(static function (Committee $committee) {
                return $committee->getUuidAsString();
            })->toArray(),
            $delegatedAccess->getRestrictedCities(),
        );
    }
}
