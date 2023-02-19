<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219152300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE event_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, event_name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, quantity INT DEFAULT NULL, target_variable VARCHAR(255) DEFAULT NULL, variable_holder_class VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E613582E5E237E06 ON event_config (name)');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD triggered_event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD revert_on_remove BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP triggered_event');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP applies_on');
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER target_event DROP NOT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER apply_on_action_parameter DROP NOT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config RENAME COLUMN modifier_holder_class TO modifier_range');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD CONSTRAINT FK_DF12FDD55A5A6FED FOREIGN KEY (triggered_event_id) REFERENCES event_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DF12FDD55A5A6FED ON abstract_modifier_config (triggered_event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP CONSTRAINT FK_DF12FDD55A5A6FED');
        $this->addSql('DROP SEQUENCE event_config_id_seq CASCADE');
        $this->addSql('DROP TABLE event_config');
        $this->addSql('DROP INDEX IDX_DF12FDD55A5A6FED');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD triggered_event VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD applies_on VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP triggered_event_id');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP revert_on_remove');
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER target_event SET NOT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER apply_on_action_parameter SET NOT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config RENAME COLUMN modifier_range TO modifier_holder_class');
    }
}
