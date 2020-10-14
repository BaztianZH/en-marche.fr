<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201014164756 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_official_report (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, mime_type VARCHAR(30) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_8D80D38525F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_official_report ADD CONSTRAINT FK_8D80D38525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_official_report');
    }
}
