<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123203608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE com_manager_announcement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE com_manager_announcement (id INT NOT NULL, com_manager_id INT DEFAULT NULL, announcement TEXT DEFAULT \'\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98F9B715CE8BC44F ON com_manager_announcement (com_manager_id)');
        $this->addSql('ALTER TABLE com_manager_announcement ADD CONSTRAINT FK_98F9B715CE8BC44F FOREIGN KEY (com_manager_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sanction_evidence ADD com_manager_announcement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F501349D6F4 FOREIGN KEY (com_manager_announcement_id) REFERENCES com_manager_announcement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_31059F501349D6F4 ON sanction_evidence (com_manager_announcement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F501349D6F4');
        $this->addSql('DROP SEQUENCE com_manager_announcement_id_seq CASCADE');
        $this->addSql('ALTER TABLE com_manager_announcement DROP CONSTRAINT FK_98F9B715CE8BC44F');
        $this->addSql('DROP TABLE com_manager_announcement');
        $this->addSql('DROP INDEX IDX_31059F501349D6F4');
        $this->addSql('ALTER TABLE sanction_evidence DROP com_manager_announcement_id');
    }
}
