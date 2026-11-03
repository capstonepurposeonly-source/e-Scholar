<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_Announcements extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create announcements table for student dashboard';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE announcements (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT \'general\',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            publish_date DATETIME DEFAULT NULL,
            expiry_date DATETIME DEFAULT NULL,
            created_by INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX idx_active (is_active),
            INDEX idx_publish_date (publish_date),
            INDEX idx_created_by (created_by)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE announcements');
    }
}
