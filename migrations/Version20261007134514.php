<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create student table for e-Scholar system
 */
final class Version20261007134514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create student table with all required fields for scholarship management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE student (
            id INT AUTO_INCREMENT NOT NULL,
            student_number VARCHAR(50) NOT NULL,
            scholarship_type VARCHAR(100) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            extension_name VARCHAR(20) DEFAULT NULL,
            date_of_birth DATE NOT NULL,
            place_of_birth VARCHAR(255) NOT NULL,
            sex VARCHAR(20) NOT NULL,
            civil_status VARCHAR(50) NOT NULL,
            religion VARCHAR(100) DEFAULT NULL,
            campus VARCHAR(100) NOT NULL,
            course VARCHAR(255) NOT NULL,
            year_level VARCHAR(50) NOT NULL,
            gwa VARCHAR(10) DEFAULT NULL,
            semester VARCHAR(50) NOT NULL,
            academic_year VARCHAR(50) NOT NULL,
            high_school VARCHAR(255) DEFAULT NULL,
            high_school_year VARCHAR(10) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            alternate_contact_number VARCHAR(20) DEFAULT NULL,
            permanent_address TEXT NOT NULL,
            current_address TEXT DEFAULT NULL,
            barangay VARCHAR(100) NOT NULL,
            municipality VARCHAR(100) NOT NULL,
            province VARCHAR(100) NOT NULL,
            zip_code VARCHAR(10) DEFAULT NULL,
            father_name VARCHAR(255) DEFAULT NULL,
            father_occupation VARCHAR(255) DEFAULT NULL,
            father_contact_number VARCHAR(20) DEFAULT NULL,
            mother_name VARCHAR(255) DEFAULT NULL,
            mother_occupation VARCHAR(255) DEFAULT NULL,
            mother_contact_number VARCHAR(20) DEFAULT NULL,
            guardian_name VARCHAR(255) DEFAULT NULL,
            guardian_relationship VARCHAR(100) DEFAULT NULL,
            guardian_contact_number VARCHAR(20) DEFAULT NULL,
            number_of_siblings VARCHAR(10) DEFAULT NULL,
            family_monthly_income VARCHAR(50) DEFAULT NULL,
            profile_picture_path VARCHAR(255) DEFAULT NULL,
            registration_status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_B723AF33D5DF7231 (student_number),
            INDEX IDX_CAMPUS (campus),
            INDEX IDX_YEAR_LEVEL (year_level),
            INDEX IDX_SCHOLARSHIP_TYPE (scholarship_type),
            INDEX IDX_REGISTRATION_STATUS (registration_status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE student');
    }
}
