<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630175432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ALTER init_hunter_points SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_spawn_rate SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_safe_cycles SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE config_difficulty ALTER starting_hunters_number_of_truce_cycles SET DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus ALTER daily_action_points_spent SET DEFAULT 0');
        $this->addSql('ALTER TABLE equipment_mechanic ADD collect_scrap_number TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic ADD collect_scrap_player_damage TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic ADD docking_place VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_daedalus_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_patrol_ship_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_player_damage TEXT DEFAULT \'[]\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.collect_scrap_number IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.collect_scrap_player_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_daedalus_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_patrol_ship_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_player_damage IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_spawn_rate DROP DEFAULT');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_safe_cycles DROP DEFAULT');
        $this->addSql('ALTER TABLE config_difficulty ALTER starting_hunters_number_of_truce_cycles DROP DEFAULT');
        $this->addSql('ALTER TABLE daedalus ALTER daily_action_points_spent DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_hunter_points DROP DEFAULT');
        $this->addSql('ALTER TABLE equipment_mechanic DROP collect_scrap_number');
        $this->addSql('ALTER TABLE equipment_mechanic DROP collect_scrap_player_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP docking_place');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_daedalus_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_patrol_ship_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_player_damage');
    }
}
