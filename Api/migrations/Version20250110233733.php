<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110233733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_mechanic ADD successful_event_keys TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD failed_event_keys TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE equipment_mechanic ADD damage_spread TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.successful_event_keys IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.failed_event_keys IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN equipment_mechanic.damage_spread IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE event_config ADD weapon_event_type VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE event_config ADD effect_keys TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE event_config ADD end_cause VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE event_config ADD to_shooter BOOLEAN DEFAULT false');
        $this->addSql('ALTER TABLE event_config ADD injury_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_config ADD trigger_rate INT DEFAULT 100');
        $this->addSql('ALTER TABLE event_config ALTER quantity SET DEFAULT 0');
        $this->addSql('COMMENT ON COLUMN event_config.effect_keys IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config DROP weapon_event_type');
        $this->addSql('ALTER TABLE event_config DROP effect_keys');
        $this->addSql('ALTER TABLE event_config DROP end_cause');
        $this->addSql('ALTER TABLE event_config DROP to_shooter');
        $this->addSql('ALTER TABLE event_config DROP injury_name');
        $this->addSql('ALTER TABLE event_config DROP trigger_rate');
        $this->addSql('ALTER TABLE event_config ALTER quantity DROP DEFAULT');
        $this->addSql('ALTER TABLE equipment_mechanic DROP successful_event_keys');
        $this->addSql('ALTER TABLE equipment_mechanic DROP failed_event_keys');
        $this->addSql('ALTER TABLE equipment_mechanic DROP damage_spread');
    }
}
