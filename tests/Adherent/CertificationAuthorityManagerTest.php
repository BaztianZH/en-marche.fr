<?php

namespace Tests\AppBundle\Adherent;

use AppBundle\Adherent\CertificationAuthorityManager;
use AppBundle\Repository\AdherentRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CertificationAuthorityManagerTest extends WebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CertificationAuthorityManager
     */
    private $certificationAuthorityManager;

    use ControllerTestTrait;

    public function testCertify(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('lolodie.dutemps@hotnix.tld');

        self::assertFalse($adherent->isCertified());

        $this->certificationAuthorityManager->certify($adherent);

        $this->manager->refresh($adherent);

        self::assertTrue($adherent->isCertified());
    }

    public function testApprove(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $this->certificationAuthorityManager->approve($certificationRequest, $administrator);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertTrue($adherent->isCertified());
        self::assertTrue($certificationRequest->isApproved());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
    }

    public function testRefuse(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('carl999@example.fr');
        $administrator = $this->getAdministratorRepository()->findOneBy(['emailAddress' => 'superadmin@en-marche-dev.fr']);

        self::assertFalse($adherent->isCertified());

        $certificationRequest = $adherent->getPendingCertificationRequest();
        self::assertTrue($certificationRequest->isPending());
        self::assertNull($certificationRequest->getProcessedBy());

        $this->certificationAuthorityManager->refuse($certificationRequest, $administrator);

        $this->manager->refresh($adherent);
        $this->manager->refresh($certificationRequest);

        self::assertFalse($adherent->isCertified());
        self::assertTrue($certificationRequest->isRefused());
        self::assertSame($administrator, $certificationRequest->getProcessedBy());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->certificationAuthorityManager = new CertificationAuthorityManager($this->manager);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->adherentRepository = null;
        $this->certificationAuthorityManager = null;

        parent::tearDown();
    }
}
