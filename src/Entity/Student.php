<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $studentNumber = null;

    #[ORM\Column(length: 100)]
    private ?string $scholarshipType = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $extensionName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 255)]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 20)]
    private ?string $sex = null;

    #[ORM\Column(length: 50)]
    private ?string $civilStatus = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $religion = null;

    #[ORM\Column(length: 100)]
    private ?string $campus = null;

    #[ORM\Column(length: 255)]
    private ?string $course = null;

    #[ORM\Column(length: 50)]
    private ?string $yearLevel = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $gwa = null;

    #[ORM\Column(length: 50)]
    private ?string $semester = null;

    #[ORM\Column(length: 50)]
    private ?string $academicYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $highSchool = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $highSchoolYear = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $contactNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $alternateContactNumber = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $permanentAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $currentAddress = null;

    #[ORM\Column(length: 100)]
    private ?string $barangay = null;

    #[ORM\Column(length: 100)]
    private ?string $municipality = null;

    #[ORM\Column(length: 100)]
    private ?string $province = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fatherName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fatherOccupation = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fatherContactNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motherName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motherOccupation = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $motherContactNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $guardianName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $guardianRelationship = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $guardianContactNumber = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numberOfSiblings = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $familyMonthlyIncome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicturePath = null;

    #[ORM\Column(length: 50)]
    private ?string $registrationStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentNumber(): ?string
    {
        return $this->studentNumber;
    }

    public function setStudentNumber(string $studentNumber): static
    {
        $this->studentNumber = $studentNumber;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): static
    {
        $this->middleName = $middleName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getExtensionName(): ?string
    {
        return $this->extensionName;
    }

    public function setExtensionName(?string $extensionName): static
    {
        $this->extensionName = $extensionName;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(string $placeOfBirth): static
    {
        $this->placeOfBirth = $placeOfBirth;
        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): static
    {
        $this->sex = $sex;
        return $this;
    }

    public function getCivilStatus(): ?string
    {
        return $this->civilStatus;
    }

    public function setCivilStatus(string $civilStatus): static
    {
        $this->civilStatus = $civilStatus;
        return $this;
    }

    public function getReligion(): ?string
    {
        return $this->religion;
    }

    public function setReligion(?string $religion): static
    {
        $this->religion = $religion;
        return $this;
    }

    public function getCampus(): ?string
    {
        return $this->campus;
    }

    public function setCampus(string $campus): static
    {
        $this->campus = $campus;
        return $this;
    }

    public function getCourse(): ?string
    {
        return $this->course;
    }

    public function setCourse(string $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getYearLevel(): ?string
    {
        return $this->yearLevel;
    }

    public function setYearLevel(string $yearLevel): static
    {
        $this->yearLevel = $yearLevel;
        return $this;
    }

    public function getGwa(): ?string
    {
        return $this->gwa;
    }

    public function setGwa(?string $gwa): static
    {
        $this->gwa = $gwa;
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

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    public function setAcademicYear(string $academicYear): static
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    public function getHighSchool(): ?string
    {
        return $this->highSchool;
    }

    public function setHighSchool(?string $highSchool): static
    {
        $this->highSchool = $highSchool;
        return $this;
    }

    public function getHighSchoolYear(): ?string
    {
        return $this->highSchoolYear;
    }

    public function setHighSchoolYear(?string $highSchoolYear): static
    {
        $this->highSchoolYear = $highSchoolYear;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }

    public function setContactNumber(string $contactNumber): static
    {
        $this->contactNumber = $contactNumber;
        return $this;
    }

    public function getAlternateContactNumber(): ?string
    {
        return $this->alternateContactNumber;
    }

    public function setAlternateContactNumber(?string $alternateContactNumber): static
    {
        $this->alternateContactNumber = $alternateContactNumber;
        return $this;
    }

    public function getPermanentAddress(): ?string
    {
        return $this->permanentAddress;
    }

    public function setPermanentAddress(string $permanentAddress): static
    {
        $this->permanentAddress = $permanentAddress;
        return $this;
    }

    public function getCurrentAddress(): ?string
    {
        return $this->currentAddress;
    }

    public function setCurrentAddress(?string $currentAddress): static
    {
        $this->currentAddress = $currentAddress;
        return $this;
    }

    public function getBarangay(): ?string
    {
        return $this->barangay;
    }

    public function setBarangay(string $barangay): static
    {
        $this->barangay = $barangay;
        return $this;
    }

    public function getMunicipality(): ?string
    {
        return $this->municipality;
    }

    public function setMunicipality(string $municipality): static
    {
        $this->municipality = $municipality;
        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): static
    {
        $this->province = $province;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getFatherName(): ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(?string $fatherName): static
    {
        $this->fatherName = $fatherName;
        return $this;
    }

    public function getFatherOccupation(): ?string
    {
        return $this->fatherOccupation;
    }

    public function setFatherOccupation(?string $fatherOccupation): static
    {
        $this->fatherOccupation = $fatherOccupation;
        return $this;
    }

    public function getFatherContactNumber(): ?string
    {
        return $this->fatherContactNumber;
    }

    public function setFatherContactNumber(?string $fatherContactNumber): static
    {
        $this->fatherContactNumber = $fatherContactNumber;
        return $this;
    }

    public function getMotherName(): ?string
    {
        return $this->motherName;
    }

    public function setMotherName(?string $motherName): static
    {
        $this->motherName = $motherName;
        return $this;
    }

    public function getMotherOccupation(): ?string
    {
        return $this->motherOccupation;
    }

    public function setMotherOccupation(?string $motherOccupation): static
    {
        $this->motherOccupation = $motherOccupation;
        return $this;
    }

    public function getMotherContactNumber(): ?string
    {
        return $this->motherContactNumber;
    }

    public function setMotherContactNumber(?string $motherContactNumber): static
    {
        $this->motherContactNumber = $motherContactNumber;
        return $this;
    }

    public function getGuardianName(): ?string
    {
        return $this->guardianName;
    }

    public function setGuardianName(?string $guardianName): static
    {
        $this->guardianName = $guardianName;
        return $this;
    }

    public function getGuardianRelationship(): ?string
    {
        return $this->guardianRelationship;
    }

    public function setGuardianRelationship(?string $guardianRelationship): static
    {
        $this->guardianRelationship = $guardianRelationship;
        return $this;
    }

    public function getGuardianContactNumber(): ?string
    {
        return $this->guardianContactNumber;
    }

    public function setGuardianContactNumber(?string $guardianContactNumber): static
    {
        $this->guardianContactNumber = $guardianContactNumber;
        return $this;
    }

    public function getNumberOfSiblings(): ?string
    {
        return $this->numberOfSiblings;
    }

    public function setNumberOfSiblings(?string $numberOfSiblings): static
    {
        $this->numberOfSiblings = $numberOfSiblings;
        return $this;
    }

    public function getFamilyMonthlyIncome(): ?string
    {
        return $this->familyMonthlyIncome;
    }

    public function setFamilyMonthlyIncome(?string $familyMonthlyIncome): static
    {
        $this->familyMonthlyIncome = $familyMonthlyIncome;
        return $this;
    }

    public function getProfilePicturePath(): ?string
    {
        return $this->profilePicturePath;
    }

    public function setProfilePicturePath(?string $profilePicturePath): static
    {
        $this->profilePicturePath = $profilePicturePath;
        return $this;
    }

    public function getRegistrationStatus(): ?string
    {
        return $this->registrationStatus;
    }

    public function setRegistrationStatus(string $registrationStatus): static
    {
        $this->registrationStatus = $registrationStatus;
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
}
