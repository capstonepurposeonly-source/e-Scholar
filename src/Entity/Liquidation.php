<?php

namespace App\Entity;

use App\Repository\LiquidationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiquidationRepository::class)]
#[ORM\Table(name: 'liquidations')]
#[ORM\HasLifecycleCallbacks]
class Liquidation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Student $student = null;

    #[ORM\Column(length: 100)]
    private ?string $scholarshipType = null;

    #[ORM\Column(length: 20)]
    private ?string $academicYear = null;

    #[ORM\Column(length: 50)]
    private ?string $semester = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $deadlineDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $submissionDate = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'Pending';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarks = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentsUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $approvedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $approvedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->status = 'Pending';
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
        
        // Auto-update status to Overdue if deadline passed and still Pending
        if ($this->status === 'Pending' && $this->deadlineDate < new \DateTime('today')) {
            $this->status = 'Overdue';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getScholarshipType(): ?string
    {
        return $this->scholarshipType;
    }

    public function setScholarshipType(string $scholarshipType): static
    {
        $this->scholarshipType = $scholarshipType;
        return $this;
    }

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    public function setAcademicYear(string $academicYear): static
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(string $semester): static
    {
        $this->semester = $semester;
        return $this;
    }

    public function getDeadlineDate(): ?\DateTimeInterface
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(\DateTimeInterface $deadlineDate): static
    {
        $this->deadlineDate = $deadlineDate;
        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(?\DateTimeInterface $submissionDate): static
    {
        $this->submissionDate = $submissionDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): static
    {
        $this->remarks = $remarks;
        return $this;
    }

    public function getDocumentsUrl(): ?string
    {
        return $this->documentsUrl;
    }

    public function setDocumentsUrl(?string $documentsUrl): static
    {
        $this->documentsUrl = $documentsUrl;
        return $this;
    }

    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?int $approvedBy): static
    {
        $this->approvedBy = $approvedBy;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeInterface $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Calculate days until deadline
     */
    public function getDaysUntilDeadline(): int
    {
        $now = new \DateTime('today');
        $deadline = clone $this->deadlineDate;
        $diff = $now->diff($deadline);
        
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Check if liquidation is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'Pending' && $this->deadlineDate < new \DateTime('today');
    }

    /**
     * Get deadline status label
     */
    public function getDeadlineStatus(): string
    {
        if ($this->status !== 'Pending') {
            return $this->status;
        }
        
        $daysUntil = $this->getDaysUntilDeadline();
        
        if ($daysUntil < 0) {
            return 'Overdue';
        } elseif ($daysUntil <= 3) {
            return 'Due Soon';
        } else {
            return 'On Time';
        }
    }
}
