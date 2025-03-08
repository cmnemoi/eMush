<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250308091447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE unique_items_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE unique_items (id INT NOT NULL, starting_blueprints TEXT DEFAULT \'a:0:{}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN unique_items.starting_blueprints IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE config_daedalus ADD starting_random_blueprint_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE config_daedalus ADD random_blueprints TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN config_daedalus.random_blueprints IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE daedalus ADD unique_items_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760A88056E8B FOREIGN KEY (unique_items_id) REFERENCES unique_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71DA760A88056E8B ON daedalus (unique_items_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus DROP CONSTRAINT FK_71DA760A88056E8B');
        $this->addSql('DROP SEQUENCE unique_items_id_seq CASCADE');
        $this->addSql('DROP TABLE unique_items');
        $this->addSql('DROP INDEX UNIQ_71DA760A88056E8B');
        $this->addSql('ALTER TABLE daedalus DROP unique_items_id');
        $this->addSql('ALTER TABLE config_daedalus DROP starting_random_blueprint_count');
        $this->addSql('ALTER TABLE config_daedalus DROP random_blueprints');
    }
}
