<?php

namespace AppBundle\Adherent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;

class CertificationAuthorityManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function certify(Adherent $adherent): void
    {
        $this->certifyAdherent($adherent);

        $this->entityManager->flush();
    }

    public function uncertify(Adherent $adherent): void
    {
        $adherent->uncertify();

        $this->entityManager->flush();
    }

    public function approve(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->approve();
        $certificationRequest->process($administrator);

        $this->certifyAdherent($certificationRequest->getAdherent());

        $this->entityManager->flush();
    }

    public function refuse(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->refuse();
        $certificationRequest->process($administrator);

        $this->entityManager->flush();
    }

    public function block(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->block();
        $certificationRequest->process($administrator);

        $this->entityManager->flush();
    }

    private function certifyAdherent(Adherent $adherent): void
    {
        $adherent->certify();
    }
}
