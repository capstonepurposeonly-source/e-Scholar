<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_Liquidations extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create liquidations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE liquidations (
            id INT AUTO_INCREMENT NOT NULL,
            student_id INT NOT NULL,
            scholarship_type VARCHAR(100) NOT NULL,
            academic_year VARCHAR(20) NOT NULL,
            semester VARCHAR(50) NOT NULL,
            deadline_date DATE NOT NULL,
            submission_date DATETIME DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT \'Pending\',
            amount DECIMAL(10, 2) DEFAULT NULL,
            remarks TEXT DEFAULT NULL,
            documents_url VARCHAR(255) DEFAULT NULL,
            approved_by INT DEFAULT NULL,
            approved_at DATETIME DEFAULT NULL,
            rejection_reason TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_STUDENT (student_id),
            INDEX IDX_STATUS (status),
            INDEX IDX_DEADLINE (deadline_date),
            PRIMARY KEY(id),
            CONSTRAINT FK_LIQUIDATION_STUDENT FOREIGN KEY (student_id)
                REFERENCES students (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE liquidations');
    }
}
