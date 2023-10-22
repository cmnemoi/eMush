<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231022083001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE exploration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE exploration_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE exploration (id INT NOT NULL, planet_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC0F0AB3A25E9820 ON exploration (planet_id)');
        $this->addSql('CREATE TABLE exploration_log (id INT NOT NULL, exploration_id INT DEFAULT NULL, planet_sector_name VARCHAR(255) NOT NULL, event_name VARCHAR(255) NOT NULL, event_description VARCHAR(255) NOT NULL, event_outcome VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98FCE691CB0970CE ON exploration_log (exploration_id)');
        $this->addSql('ALTER TABLE exploration ADD CONSTRAINT FK_AC0F0AB3A25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exploration_log ADD CONSTRAINT FK_98FCE691CB0970CE FOREIGN KEY (exploration_id) REFERENCES exploration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE exploration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE exploration_log_id_seq CASCADE');
        $this->addSql('ALTER TABLE exploration DROP CONSTRAINT FK_AC0F0AB3A25E9820');
        $this->addSql('ALTER TABLE exploration_log DROP CONSTRAINT FK_98FCE691CB0970CE');
        $this->addSql('DROP TABLE exploration');
        $this->addSql('DROP TABLE exploration_log');
    }
}
