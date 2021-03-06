<?php

namespace Tests\App\EntityListener;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class RevokeDelegatedAccessListenerTest extends WebTestCase
{
    use ControllerTestTrait;

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }

    public function testDeputyDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $deputy = $this->manager->getRepository(Adherent::class)->findOneByEmail('deputy-ch-li@en-marche-dev.fr');

        $this->assertCount(3, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $deputy]));

        $deputy->setManagedDistrict(null);
        $this->manager->flush();

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $deputy]));
    }

    public function testSenatorDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $senator = $this->manager->getRepository(Adherent::class)->findOneByEmail('senateur@en-marche-dev.fr');

        $this->assertCount(2, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $senator]));

        $senator->setSenatorArea(null);
        $this->manager->flush();

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $senator]));
    }

    public function testReferentDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $referent = $this->manager->getRepository(Adherent::class)->findOneByEmail('referent@en-marche-dev.fr');

        $this->assertCount(1, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $referent]));

        $referent->setManagedArea(null);
        $this->manager->flush();

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $referent]));
    }
}
