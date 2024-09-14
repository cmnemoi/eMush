<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240903203347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE title_priority_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE title_priority (id INT NOT NULL, daedalus_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, priority TEXT DEFAULT \'a:0:{}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B3D698674B5A52D ON title_priority (daedalus_id)');
        $this->addSql('COMMENT ON COLUMN title_priority.priority IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE title_priority ADD CONSTRAINT FK_4B3D698674B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE title_priority_id_seq CASCADE');
        $this->addSql('ALTER TABLE title_priority DROP CONSTRAINT FK_4B3D698674B5A52D');
        $this->addSql('DROP TABLE title_priority');
    }
}
