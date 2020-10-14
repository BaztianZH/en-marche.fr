<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\OfficialReportRepository"))
 * @ORM\Table(name="territorial_council_official_report")
 */
class OfficialReport
{
    use EntityIdentityTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Entity\TerritorialCouncil\OfficialReportTypeEnum", "toArray"},
     *     message="territorail_council.official_report.invalid_choice",
     *     strict=true
     * )
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=30)
     */
    private $mimeType;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf"
     *     },
     *     mimeTypesMessage="territorail_council.official_report.curriculum.mime_type"
     * )
     */
    private $file;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="certificationRequests")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->createdAt = new \DateTime();
        $this->adherent = $adherent;
        $this->uuid = Uuid::uuid4();
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getDocumentExtension(): ?string
    {
        if (!$extension = strrchr($this->name, '.')) {
            return null;
        }

        return substr($extension, 1);
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getPath(): string
    {
        return \sprintf('%s/%s', 'files/territorial_council/official_reports', $this->name);
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
