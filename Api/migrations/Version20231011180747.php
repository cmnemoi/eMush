<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011180747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_config_planet_sector_config (game_config_id INT NOT NULL, planet_sector_config_id INT NOT NULL, PRIMARY KEY(game_config_id, planet_sector_config_id))');
        $this->addSql('CREATE INDEX IDX_94724907F67DC781 ON game_config_planet_sector_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_947249077931EF3D ON game_config_planet_sector_config (planet_sector_config_id)');
        $this->addSql('ALTER TABLE game_config_planet_sector_config ADD CONSTRAINT FK_94724907F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_planet_sector_config ADD CONSTRAINT FK_947249077931EF3D FOREIGN KEY (planet_sector_config_id) REFERENCES planet_sector_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE planet DROP CONSTRAINT fk_68136aa574b5a52d');
        $this->addSql('DROP INDEX unique_planet_for_daedalus');
        $this->addSql('DROP INDEX idx_68136aa574b5a52d');
        $this->addSql('ALTER TABLE planet DROP daedalus_id');
        $this->addSql('ALTER TABLE planet_sector_config ADD sector_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_config_planet_sector_config DROP CONSTRAINT FK_94724907F67DC781');
        $this->addSql('ALTER TABLE game_config_planet_sector_config DROP CONSTRAINT FK_947249077931EF3D');
        $this->addSql('DROP TABLE game_config_planet_sector_config');
        $this->addSql('ALTER TABLE planet_sector_config DROP sector_name');
        $this->addSql('ALTER TABLE planet ADD daedalus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT fk_68136aa574b5a52d FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX unique_planet_for_daedalus ON planet (name, orientation, distance, daedalus_id)');
        $this->addSql('CREATE INDEX idx_68136aa574b5a52d ON planet (daedalus_id)');
    }
}
