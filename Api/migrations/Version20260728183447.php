<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728183447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE planet_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_planet_config (game_config_id INT NOT NULL, planet_config_id INT NOT NULL, PRIMARY KEY(game_config_id, planet_config_id))');
        $this->addSql('CREATE INDEX IDX_9AB29CD3F67DC781 ON game_config_planet_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_9AB29CD3A516597B ON game_config_planet_config (planet_config_id)');
        $this->addSql('CREATE TABLE planet_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, maximum_sectors TEXT DEFAULT \'a:1:{s:0:"";i:0;}\' NOT NULL, sectors_weight TEXT DEFAULT \'a:1:{s:0:"";i:0;}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN planet_config.maximum_sectors IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN planet_config.sectors_weight IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE game_config_planet_config ADD CONSTRAINT FK_9AB29CD3F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_planet_config ADD CONSTRAINT FK_9AB29CD3A516597B FOREIGN KEY (planet_config_id) REFERENCES planet_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE planet_sector_config DROP weight_at_planet_generation');
        $this->addSql('ALTER TABLE planet_sector_config DROP max_per_planet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE planet_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_planet_config DROP CONSTRAINT FK_9AB29CD3F67DC781');
        $this->addSql('ALTER TABLE game_config_planet_config DROP CONSTRAINT FK_9AB29CD3A516597B');
        $this->addSql('DROP TABLE game_config_planet_config');
        $this->addSql('DROP TABLE planet_config');
        $this->addSql('ALTER TABLE planet_sector_config ADD weight_at_planet_generation INT NOT NULL');
        $this->addSql('ALTER TABLE planet_sector_config ADD max_per_planet INT NOT NULL');
    }
}
