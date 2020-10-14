<?php

namespace App\TerritorialCouncil\Convocation;

use App\Entity\TerritorialCouncil\OfficialReport;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OfficialReportManager
{
    private $storage;
    private $entityManager;

    public function __construct(FilesystemInterface $storage, EntityManagerInterface $entityManager)
    {
        $this->storage = $storage;
        $this->entityManager = $entityManager;
    }

    public function handleRequest(OfficialReport $report): void
    {
        $this->uploadDocument($report);

        $this->entityManager->flush();
    }

    public function uploadDocument(OfficialReport $report): void
    {
        if (!$report->getFile() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $this->storage->put($report->getPath(), file_get_contents($report->getFile()->getPathname()));
    }
}
