<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323145445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus DROP CONSTRAINT fk_cc1298d4d5f7b64e');
        $this->addSql('DROP SEQUENCE config_random_item_place_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE random_item_places_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE daedalus_config_random_item_places (daedalus_config_id INT NOT NULL, random_item_places_id INT NOT NULL, PRIMARY KEY(daedalus_config_id, random_item_places_id))');
        $this->addSql('CREATE INDEX IDX_44FB57D3201E3C43 ON daedalus_config_random_item_places (daedalus_config_id)');
        $this->addSql('CREATE INDEX IDX_44FB57D3D5F7B64E ON daedalus_config_random_item_places (random_item_places_id)');
        $this->addSql('CREATE TABLE random_item_places (id INT NOT NULL, name VARCHAR(255) NOT NULL, places TEXT NOT NULL, items TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN random_item_places.places IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN random_item_places.items IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE daedalus_config_random_item_places ADD CONSTRAINT FK_44FB57D3201E3C43 FOREIGN KEY (daedalus_config_id) REFERENCES config_daedalus (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE daedalus_config_random_item_places ADD CONSTRAINT FK_44FB57D3D5F7B64E FOREIGN KEY (random_item_places_id) REFERENCES random_item_places (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE config_random_item_place');
        $this->addSql('DROP INDEX uniq_cc1298d4d5f7b64e');
        $this->addSql('ALTER TABLE config_daedalus DROP random_item_places_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE random_item_places_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE config_random_item_place_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE config_random_item_place (id INT NOT NULL, name VARCHAR(255) NOT NULL, places TEXT NOT NULL, items TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN config_random_item_place.places IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN config_random_item_place.items IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE daedalus_config_random_item_places DROP CONSTRAINT FK_44FB57D3201E3C43');
        $this->addSql('ALTER TABLE daedalus_config_random_item_places DROP CONSTRAINT FK_44FB57D3D5F7B64E');
        $this->addSql('DROP TABLE daedalus_config_random_item_places');
        $this->addSql('DROP TABLE random_item_places');
        $this->addSql('ALTER TABLE config_daedalus ADD random_item_places_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE config_daedalus ADD CONSTRAINT fk_cc1298d4d5f7b64e FOREIGN KEY (random_item_places_id) REFERENCES config_random_item_place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_cc1298d4d5f7b64e ON config_daedalus (random_item_places_id)');
    }
}
