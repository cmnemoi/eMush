<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024194258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE action ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE alert ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE alert_element ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE character_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE closed_exploration ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE closed_player ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE commander_mission ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE communication_channel ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE communication_channel_player ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE config_daedalus ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE config_difficulty ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE config_game ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE config_localization ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE config_random_item_place ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE consumable_effect ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE daedalus ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE daedalus_closed ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE daedalus_info ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_cause_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_consummable ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_consummable_attribute ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_consummable_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_player ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE drone_info ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE equipment_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE exploration ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE exploration_log ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE game_variable ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE game_variable_collection ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE hunter ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE hunter_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE hunter_target ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE message ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE modifier_activation_requirement ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE modifier_holder ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE modifier_provider ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE neron ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE news ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE place_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE planet ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE planet_name ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE planet_sector ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE planet_sector_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE plant_effect ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE player ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE player_notification ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE project ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE project_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE project_requirement ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE project_requirement ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE project_requirement ALTER type SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE project_requirement ALTER target SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE replace_equipment_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE room ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE room_log ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE sanction_evidence ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE skill ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE skill_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE spawn_equipment_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE status_target ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE title_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE title_priority ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE users ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_random_item_place DROP version');
        $this->addSql('ALTER TABLE skill_config DROP version');
        $this->addSql('ALTER TABLE disease_cause_config DROP version');
        $this->addSql('ALTER TABLE place_config DROP version');
        $this->addSql('ALTER TABLE sanction_evidence DROP version');
        $this->addSql('ALTER TABLE triumph_config DROP version');
        $this->addSql('ALTER TABLE action DROP version');
        $this->addSql('ALTER TABLE drone_info DROP version');
        $this->addSql('ALTER TABLE config_game DROP version');
        $this->addSql('ALTER TABLE closed_player DROP version');
        $this->addSql('ALTER TABLE daedalus DROP version');
        $this->addSql('ALTER TABLE config_daedalus DROP version');
        $this->addSql('ALTER TABLE replace_equipment_config DROP version');
        $this->addSql('ALTER TABLE daedalus_closed DROP version');
        $this->addSql('ALTER TABLE project_requirement DROP version');
        $this->addSql('ALTER TABLE project_requirement ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE project_requirement ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE project_requirement ALTER target DROP DEFAULT');
        $this->addSql('ALTER TABLE project DROP version');
        $this->addSql('ALTER TABLE spawn_equipment_config DROP version');
        $this->addSql('ALTER TABLE project_config DROP version');
        $this->addSql('ALTER TABLE moderationSanction DROP version');
        $this->addSql('ALTER TABLE modifier_activation_requirement DROP version');
        $this->addSql('ALTER TABLE title_priority DROP version');
        $this->addSql('ALTER TABLE closed_exploration DROP version');
        $this->addSql('ALTER TABLE game_variable_collection DROP version');
        $this->addSql('ALTER TABLE player_notification DROP version');
        $this->addSql('ALTER TABLE alert DROP version');
        $this->addSql('ALTER TABLE commander_mission DROP version');
        $this->addSql('ALTER TABLE alert_element DROP version');
        $this->addSql('ALTER TABLE modifier_holder DROP version');
        $this->addSql('ALTER TABLE exploration_log DROP version');
        $this->addSql('ALTER TABLE exploration DROP version');
        $this->addSql('ALTER TABLE player_info DROP version');
        $this->addSql('ALTER TABLE modifier_provider DROP version');
        $this->addSql('ALTER TABLE planet_name DROP version');
        $this->addSql('ALTER TABLE title_config DROP version');
        $this->addSql('ALTER TABLE planet_sector_config DROP version');
        $this->addSql('ALTER TABLE equipment_mechanic DROP version');
        $this->addSql('ALTER TABLE room DROP version');
        $this->addSql('ALTER TABLE planet DROP version');
        $this->addSql('ALTER TABLE planet_sector DROP version');
        $this->addSql('ALTER TABLE hunter_target DROP version');
        $this->addSql('ALTER TABLE equipment_config DROP version');
        $this->addSql('ALTER TABLE character_config DROP version');
        $this->addSql('ALTER TABLE hunter_config DROP version');
        $this->addSql('ALTER TABLE hunter DROP version');
        $this->addSql('ALTER TABLE neron DROP version');
        $this->addSql('ALTER TABLE message DROP version');
        $this->addSql('ALTER TABLE disease_config DROP version');
        $this->addSql('ALTER TABLE news DROP version');
        $this->addSql('ALTER TABLE room_log DROP version');
        $this->addSql('ALTER TABLE users DROP version');
        $this->addSql('ALTER TABLE game_modifier DROP version');
        $this->addSql('ALTER TABLE status_target DROP version');
        $this->addSql('ALTER TABLE player DROP version');
        $this->addSql('ALTER TABLE config_difficulty DROP version');
        $this->addSql('ALTER TABLE daedalus_info DROP version');
        $this->addSql('ALTER TABLE plant_effect DROP version');
        $this->addSql('ALTER TABLE game_variable DROP version');
        $this->addSql('ALTER TABLE disease_player DROP version');
        $this->addSql('ALTER TABLE communication_channel DROP version');
        $this->addSql('ALTER TABLE disease_consummable_attribute DROP version');
        $this->addSql('ALTER TABLE disease_consummable DROP version');
        $this->addSql('ALTER TABLE config_localization DROP version');
        $this->addSql('ALTER TABLE communication_channel_player DROP version');
        $this->addSql('ALTER TABLE skill DROP version');
        $this->addSql('ALTER TABLE consumable_effect DROP version');
        $this->addSql('ALTER TABLE disease_consummable_config DROP version');
    }
}
