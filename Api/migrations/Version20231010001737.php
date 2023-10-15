<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010001737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE planet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE planet_sector_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE planet_sector_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE planet (id INT NOT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, orientation VARCHAR(255) NOT NULL, distance INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_planet_space_coordinates ON planet (orientation, distance)');
        $this->addSql('CREATE TABLE planet_sector (id INT NOT NULL, planet_sector_config_id INT DEFAULT NULL, planet_id INT DEFAULT NULL, is_revealed BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D7BEDC207931EF3D ON planet_sector (planet_sector_config_id)');
        $this->addSql('CREATE INDEX IDX_D7BEDC20A25E9820 ON planet_sector (planet_id)');
        $this->addSql('CREATE TABLE planet_sector_config (id INT NOT NULL, weight_at_planet_generation INT NOT NULL, weight_at_planet_analysis INT NOT NULL, weight_at_planet_exploration INT NOT NULL, max_per_planet INT NOT NULL, exploration_events TEXT DEFAULT \'a:1:{s:0:"";i:0;}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN planet_sector_config.exploration_events IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE planet_sector ADD CONSTRAINT FK_D7BEDC207931EF3D FOREIGN KEY (planet_sector_config_id) REFERENCES planet_sector_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE planet_sector ADD CONSTRAINT FK_D7BEDC20A25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE planet_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE planet_sector_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE planet_sector_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE planet_sector DROP CONSTRAINT FK_D7BEDC207931EF3D');
        $this->addSql('ALTER TABLE planet_sector DROP CONSTRAINT FK_D7BEDC20A25E9820');
        $this->addSql('DROP TABLE planet');
        $this->addSql('DROP TABLE planet_sector');
        $this->addSql('DROP TABLE planet_sector_config');
    }
}
