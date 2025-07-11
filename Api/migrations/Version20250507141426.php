<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507141426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_config ADD collect_scrap_number TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD collect_scrap_patrol_ship_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD collect_scrap_player_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD failed_manoeuvre_daedalus_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD failed_manoeuvre_patrol_ship_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD failed_manoeuvre_player_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_config ADD number_of_exploration_steps INT DEFAULT 0');
        $this->addSql('COMMENT ON COLUMN equipment_config.collect_scrap_number IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_config.collect_scrap_patrol_ship_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_config.collect_scrap_player_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_config.failed_manoeuvre_daedalus_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_config.failed_manoeuvre_patrol_ship_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_config.failed_manoeuvre_player_damage IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE equipment_mechanic DROP collect_scrap_number');
        $this->addSql('ALTER TABLE equipment_mechanic DROP collect_scrap_player_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP docking_place');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_daedalus_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_patrol_ship_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_manoeuvre_player_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP collect_scrap_patrol_ship_damage');
        $this->addSql('ALTER TABLE equipment_mechanic DROP number_of_exploration_steps');
        $this->addSql('ALTER TABLE game_equipment ADD docking_place VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE game_equipment ADD patrol_ship_name VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE place_config ADD patrol_ship_names TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN place_config.patrol_ship_names IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place_config DROP patrol_ship_names');
        $this->addSql('ALTER TABLE game_equipment DROP docking_place');
        $this->addSql('ALTER TABLE game_equipment DROP patrol_ship_name');
        $this->addSql('ALTER TABLE equipment_mechanic ADD collect_scrap_number TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD collect_scrap_player_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD docking_place VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_daedalus_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_patrol_ship_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_manoeuvre_player_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD collect_scrap_patrol_ship_damage TEXT DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD number_of_exploration_steps INT DEFAULT 0');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.collect_scrap_number IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.collect_scrap_player_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_daedalus_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_patrol_ship_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_manoeuvre_player_damage IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.collect_scrap_patrol_ship_damage IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE equipment_config DROP collect_scrap_number');
        $this->addSql('ALTER TABLE equipment_config DROP collect_scrap_patrol_ship_damage');
        $this->addSql('ALTER TABLE equipment_config DROP collect_scrap_player_damage');
        $this->addSql('ALTER TABLE equipment_config DROP failed_manoeuvre_daedalus_damage');
        $this->addSql('ALTER TABLE equipment_config DROP failed_manoeuvre_patrol_ship_damage');
        $this->addSql('ALTER TABLE equipment_config DROP failed_manoeuvre_player_damage');
        $this->addSql('ALTER TABLE equipment_config DROP number_of_exploration_steps');
    }
}
