<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_StudentAuth extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add authentication and load slip fields to students table';
    }

    public function up(Schema $schema): void
    {
        // Add password field for student authentication
        $this->addSql('ALTER TABLE students ADD password VARCHAR(255) DEFAULT NULL');

        // Add load slip PDF path
        $this->addSql('ALTER TABLE students ADD load_slip_path VARCHAR(255) DEFAULT NULL');

        // Add roles for student accounts
        $this->addSql('ALTER TABLE students ADD roles JSON NOT NULL');

        // Add email verification
        $this->addSql('ALTER TABLE students ADD is_email_verified TINYINT(1) DEFAULT 0');

        // Add account activation
        $this->addSql('ALTER TABLE students ADD is_active TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students DROP password');
        $this->addSql('ALTER TABLE students DROP load_slip_path');
        $this->addSql('ALTER TABLE students DROP roles');
        $this->addSql('ALTER TABLE students DROP is_email_verified');
        $this->addSql('ALTER TABLE students DROP is_active');
    }
}
