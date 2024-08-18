<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240810170906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE modifier_holder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE modifier_provider_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE trigger_event_modifier_config_modifier_activation_requirement (trigger_event_modifier_config_id INT NOT NULL, modifier_activation_requirement_id INT NOT NULL, PRIMARY KEY(trigger_event_modifier_config_id, modifier_activation_requirement_id))');
        $this->addSql('CREATE INDEX IDX_9F59FEF932E1FF97 ON trigger_event_modifier_config_modifier_activation_requirement (trigger_event_modifier_config_id)');
        $this->addSql('CREATE INDEX IDX_9F59FEF9F299F7D7 ON trigger_event_modifier_config_modifier_activation_requirement (modifier_activation_requirement_id)');
        $this->addSql('CREATE TABLE direct_modifier_config_modifier_activation_requirement (direct_modifier_config_id INT NOT NULL, modifier_activation_requirement_id INT NOT NULL, PRIMARY KEY(direct_modifier_config_id, modifier_activation_requirement_id))');
        $this->addSql('CREATE INDEX IDX_39CC9281ED3D0A8E ON direct_modifier_config_modifier_activation_requirement (direct_modifier_config_id)');
        $this->addSql('CREATE INDEX IDX_39CC9281F299F7D7 ON direct_modifier_config_modifier_activation_requirement (modifier_activation_requirement_id)');
        $this->addSql('CREATE TABLE modifier_holder (id INT NOT NULL, player_id INT DEFAULT NULL, place_id INT DEFAULT NULL, game_equipment_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_17FE980199E6F5DF ON modifier_holder (player_id)');
        $this->addSql('CREATE INDEX IDX_17FE9801DA6A219 ON modifier_holder (place_id)');
        $this->addSql('CREATE INDEX IDX_17FE9801BFAFDD90 ON modifier_holder (game_equipment_id)');
        $this->addSql('CREATE INDEX IDX_17FE980174B5A52D ON modifier_holder (daedalus_id)');
        $this->addSql('CREATE TABLE modifier_provider (id INT NOT NULL, player_id INT DEFAULT NULL, game_equipment_id INT DEFAULT NULL, project_id INT DEFAULT NULL, status_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6709A16299E6F5DF ON modifier_provider (player_id)');
        $this->addSql('CREATE INDEX IDX_6709A162BFAFDD90 ON modifier_provider (game_equipment_id)');
        $this->addSql('CREATE INDEX IDX_6709A162166D1F9C ON modifier_provider (project_id)');
        $this->addSql('CREATE INDEX IDX_6709A1626BF700BD ON modifier_provider (status_id)');
        $this->addSql('ALTER TABLE trigger_event_modifier_config_modifier_activation_requirement ADD CONSTRAINT FK_9F59FEF932E1FF97 FOREIGN KEY (trigger_event_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trigger_event_modifier_config_modifier_activation_requirement ADD CONSTRAINT FK_9F59FEF9F299F7D7 FOREIGN KEY (modifier_activation_requirement_id) REFERENCES modifier_activation_requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE direct_modifier_config_modifier_activation_requirement ADD CONSTRAINT FK_39CC9281ED3D0A8E FOREIGN KEY (direct_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE direct_modifier_config_modifier_activation_requirement ADD CONSTRAINT FK_39CC9281F299F7D7 FOREIGN KEY (modifier_activation_requirement_id) REFERENCES modifier_activation_requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE980199E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE9801DA6A219 FOREIGN KEY (place_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE9801BFAFDD90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE980174B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A16299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162BFAFDD90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1626BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD target_filters TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN abstract_modifier_config.target_filters IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26db99e6f5df');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26dbda6a219');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26dbbfafdd90');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26db74b5a52d');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26db55284914');
        $this->addSql('DROP INDEX idx_fb26db55284914');
        $this->addSql('DROP INDEX idx_fb26db74b5a52d');
        $this->addSql('DROP INDEX idx_fb26dbbfafdd90');
        $this->addSql('DROP INDEX idx_fb26dbda6a219');
        $this->addSql('DROP INDEX idx_fb26db99e6f5df');
        $this->addSql('ALTER TABLE game_modifier ADD modifier_holder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD modifier_provider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier DROP player_id');
        $this->addSql('ALTER TABLE game_modifier DROP place_id');
        $this->addSql('ALTER TABLE game_modifier DROP game_equipment_id');
        $this->addSql('ALTER TABLE game_modifier DROP daedalus_id');
        $this->addSql('ALTER TABLE game_modifier DROP charge_id');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT FK_FB26DB841E7019 FOREIGN KEY (modifier_holder_id) REFERENCES modifier_holder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT FK_FB26DBE3D2EF15 FOREIGN KEY (modifier_provider_id) REFERENCES modifier_provider (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FB26DB841E7019 ON game_modifier (modifier_holder_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FB26DBE3D2EF15 ON game_modifier (modifier_provider_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT FK_FB26DB841E7019');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT FK_FB26DBE3D2EF15');
        $this->addSql('DROP SEQUENCE modifier_holder_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE modifier_provider_id_seq CASCADE');
        $this->addSql('ALTER TABLE trigger_event_modifier_config_modifier_activation_requirement DROP CONSTRAINT FK_9F59FEF932E1FF97');
        $this->addSql('ALTER TABLE trigger_event_modifier_config_modifier_activation_requirement DROP CONSTRAINT FK_9F59FEF9F299F7D7');
        $this->addSql('ALTER TABLE direct_modifier_config_modifier_activation_requirement DROP CONSTRAINT FK_39CC9281ED3D0A8E');
        $this->addSql('ALTER TABLE direct_modifier_config_modifier_activation_requirement DROP CONSTRAINT FK_39CC9281F299F7D7');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE980199E6F5DF');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE9801DA6A219');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE9801BFAFDD90');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE980174B5A52D');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A16299E6F5DF');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162BFAFDD90');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162166D1F9C');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1626BF700BD');
        $this->addSql('DROP TABLE trigger_event_modifier_config_modifier_activation_requirement');
        $this->addSql('DROP TABLE direct_modifier_config_modifier_activation_requirement');
        $this->addSql('DROP TABLE modifier_holder');
        $this->addSql('DROP TABLE modifier_provider');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP target_filters');
        $this->addSql('DROP INDEX UNIQ_FB26DB841E7019');
        $this->addSql('DROP INDEX UNIQ_FB26DBE3D2EF15');
        $this->addSql('ALTER TABLE game_modifier ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD game_equipment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD daedalus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD charge_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier DROP modifier_holder_id');
        $this->addSql('ALTER TABLE game_modifier DROP modifier_provider_id');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26db99e6f5df FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26dbda6a219 FOREIGN KEY (place_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26dbbfafdd90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26db74b5a52d FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26db55284914 FOREIGN KEY (charge_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fb26db55284914 ON game_modifier (charge_id)');
        $this->addSql('CREATE INDEX idx_fb26db74b5a52d ON game_modifier (daedalus_id)');
        $this->addSql('CREATE INDEX idx_fb26dbbfafdd90 ON game_modifier (game_equipment_id)');
        $this->addSql('CREATE INDEX idx_fb26dbda6a219 ON game_modifier (place_id)');
        $this->addSql('CREATE INDEX idx_fb26db99e6f5df ON game_modifier (player_id)');
    }
}
