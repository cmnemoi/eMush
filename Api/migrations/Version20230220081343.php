<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220081343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE event_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE disease_config_abstract_modifier_config (disease_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(disease_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_65A5EEFE1998F6F9 ON disease_config_abstract_modifier_config (disease_config_id)');
        $this->addSql('CREATE INDEX IDX_65A5EEFEBFA8DC8C ON disease_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('CREATE TABLE event_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, event_name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, quantity INT DEFAULT NULL, target_variable VARCHAR(255) DEFAULT NULL, variable_holder_class VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E613582E5E237E06 ON event_config (name)');
        $this->addSql('CREATE TABLE status_config_abstract_modifier_config (status_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(status_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_478A09C7AC4E86C2 ON status_config_abstract_modifier_config (status_config_id)');
        $this->addSql('CREATE INDEX IDX_478A09C7BFA8DC8C ON status_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config ADD CONSTRAINT FK_65A5EEFE1998F6F9 FOREIGN KEY (disease_config_id) REFERENCES disease_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config ADD CONSTRAINT FK_65A5EEFEBFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_abstract_modifier_config ADD CONSTRAINT FK_478A09C7AC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_abstract_modifier_config ADD CONSTRAINT FK_478A09C7BFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_variable_event_modifier_config DROP CONSTRAINT fk_a055c203ac4e86c2');
        $this->addSql('ALTER TABLE status_config_variable_event_modifier_config DROP CONSTRAINT fk_a055c20352cfb74c');
        $this->addSql('ALTER TABLE disease_config_variable_event_modifier_config DROP CONSTRAINT fk_da4ca24f1998f6f9');
        $this->addSql('ALTER TABLE disease_config_variable_event_modifier_config DROP CONSTRAINT fk_da4ca24f52cfb74c');
        $this->addSql('DROP TABLE status_config_variable_event_modifier_config');
        $this->addSql('DROP TABLE disease_config_variable_event_modifier_config');
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
        $this->addSql('CREATE TABLE status_config_variable_event_modifier_config (status_config_id INT NOT NULL, variable_event_modifier_config_id INT NOT NULL, PRIMARY KEY(status_config_id, variable_event_modifier_config_id))');
        $this->addSql('CREATE INDEX idx_a055c20352cfb74c ON status_config_variable_event_modifier_config (variable_event_modifier_config_id)');
        $this->addSql('CREATE INDEX idx_a055c203ac4e86c2 ON status_config_variable_event_modifier_config (status_config_id)');
        $this->addSql('CREATE TABLE disease_config_variable_event_modifier_config (disease_config_id INT NOT NULL, variable_event_modifier_config_id INT NOT NULL, PRIMARY KEY(disease_config_id, variable_event_modifier_config_id))');
        $this->addSql('CREATE INDEX idx_da4ca24f52cfb74c ON disease_config_variable_event_modifier_config (variable_event_modifier_config_id)');
        $this->addSql('CREATE INDEX idx_da4ca24f1998f6f9 ON disease_config_variable_event_modifier_config (disease_config_id)');
        $this->addSql('ALTER TABLE status_config_variable_event_modifier_config ADD CONSTRAINT fk_a055c203ac4e86c2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_variable_event_modifier_config ADD CONSTRAINT fk_a055c20352cfb74c FOREIGN KEY (variable_event_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_variable_event_modifier_config ADD CONSTRAINT fk_da4ca24f1998f6f9 FOREIGN KEY (disease_config_id) REFERENCES disease_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_variable_event_modifier_config ADD CONSTRAINT fk_da4ca24f52cfb74c FOREIGN KEY (variable_event_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config DROP CONSTRAINT FK_65A5EEFE1998F6F9');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config DROP CONSTRAINT FK_65A5EEFEBFA8DC8C');
        $this->addSql('ALTER TABLE status_config_abstract_modifier_config DROP CONSTRAINT FK_478A09C7AC4E86C2');
        $this->addSql('ALTER TABLE status_config_abstract_modifier_config DROP CONSTRAINT FK_478A09C7BFA8DC8C');
        $this->addSql('DROP TABLE disease_config_abstract_modifier_config');
        $this->addSql('DROP TABLE event_config');
        $this->addSql('DROP TABLE status_config_abstract_modifier_config');
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
