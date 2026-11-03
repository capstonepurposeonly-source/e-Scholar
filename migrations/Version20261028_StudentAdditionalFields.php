<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_StudentAdditionalFields extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add additional student fields: suffix, elementary, spouse info, LRN, country, and updated address fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students ADD COLUMN suffix VARCHAR(20) NULL AFTER extension_name');
        $this->addSql('ALTER TABLE students ADD COLUMN elementary VARCHAR(255) NULL AFTER high_school_year');
        $this->addSql('ALTER TABLE students ADD COLUMN elementary_year VARCHAR(10) NULL AFTER elementary');
        $this->addSql('ALTER TABLE students ADD COLUMN spouse_name VARCHAR(255) NULL AFTER guardian_contact_number');
        $this->addSql('ALTER TABLE students ADD COLUMN spouse_contact_number VARCHAR(20) NULL AFTER spouse_name');
        $this->addSql('ALTER TABLE students ADD COLUMN lrn VARCHAR(20) NULL AFTER student_number');
        $this->addSql('ALTER TABLE students ADD COLUMN country VARCHAR(100) NULL DEFAULT "Philippines" AFTER province');
        $this->addSql('ALTER TABLE students ADD COLUMN street VARCHAR(255) NULL AFTER current_address');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students DROP COLUMN suffix');
        $this->addSql('ALTER TABLE students DROP COLUMN elementary');
        $this->addSql('ALTER TABLE students DROP COLUMN elementary_year');
        $this->addSql('ALTER TABLE students DROP COLUMN spouse_name');
        $this->addSql('ALTER TABLE students DROP COLUMN spouse_contact_number');
        $this->addSql('ALTER TABLE students DROP COLUMN lrn');
        $this->addSql('ALTER TABLE students DROP COLUMN country');
        $this->addSql('ALTER TABLE students DROP COLUMN street');
    }
}
