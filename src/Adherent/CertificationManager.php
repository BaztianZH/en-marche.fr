<?php

namespace AppBundle\Adherent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertificationManager
{
    private $entityManager;
    private $storage;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $storage)
    {
        $this->entityManager = $entityManager;
        $this->storage = $storage;
    }

    public function canRequest(Adherent $adherent): bool
    {
        return !$adherent->isCertified() && !$adherent->getPendingCertificationRequest();
    }

    public function createRequest(Adherent $adherent): CertificationRequest
    {
        $adherent->startCertificationRequest();

        return $adherent->getPendingCertificationRequest();
    }

    public function handleRequest(CertificationRequest $certificationRequest): void
    {
        $this->uploadDocument($certificationRequest);

        $this->entityManager->flush();
    }

    public function uploadDocument(CertificationRequest $certificationRequest): void
    {
        if (!$certificationRequest->getDocument() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $certificationRequest->setDocumentNameFromUploadedFile($certificationRequest->getDocument());
        $path = $certificationRequest->getPathWithDirectory();

        $this->storage->put($path, file_get_contents($certificationRequest->getDocument()->getPathname()));
    }
}
