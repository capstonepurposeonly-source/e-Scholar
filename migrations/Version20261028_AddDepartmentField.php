<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_AddDepartmentField extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add department field to students table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students ADD COLUMN department VARCHAR(255) NULL AFTER course');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students DROP COLUMN department');
    }
}
