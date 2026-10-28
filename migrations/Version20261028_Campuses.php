<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028_Campuses extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create campuses table with NORSU default campuses';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE campuses (
            id INT AUTO_INCREMENT NOT NULL,
            campus_name VARCHAR(100) NOT NULL,
            campus_code VARCHAR(20) NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            contact_number VARCHAR(20) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_CAMPUS_NAME (campus_name),
            UNIQUE INDEX UNIQ_CAMPUS_CODE (campus_code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Insert NORSU default campuses
        $now = date('Y-m-d H:i:s');
        $this->addSql("INSERT INTO campuses (campus_name, campus_code, address, is_active, created_at, updated_at) VALUES
            ('NORSU Main Campus', 'NORSU-MAIN', 'Dumaguete City, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Bayawan-Sta. Catalina Campus', 'NORSU-BSC', 'Bayawan City, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Bais Campus', 'NORSU-BAIS', 'Bais City, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Guihulngan Campus', 'NORSU-GUIH', 'Guihulngan City, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Mabinay Campus', 'NORSU-MAB', 'Mabinay, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Pamplona Campus', 'NORSU-PAM', 'Pamplona, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Siaton Campus', 'NORSU-SIA', 'Siaton, Negros Oriental', 1, '$now', '$now'),
            ('NORSU Sibulan Campus', 'NORSU-SIB', 'Sibulan, Negros Oriental', 1, '$now', '$now')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE campuses');
    }
}
