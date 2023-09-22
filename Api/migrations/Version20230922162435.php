<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230922162435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE symptom_activation_requirement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE symptom_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE symptom_config_symptom_activation_requirement DROP CONSTRAINT fk_63caf879985ac9b');
        $this->addSql('ALTER TABLE symptom_config_symptom_activation_requirement DROP CONSTRAINT fk_63caf87d39ae590');
        $this->addSql('ALTER TABLE disease_config_symptom_config DROP CONSTRAINT fk_99aa55c81998f6f9');
        $this->addSql('ALTER TABLE disease_config_symptom_config DROP CONSTRAINT fk_99aa55c89985ac9b');
        $this->addSql('DROP TABLE symptom_activation_requirement');
        $this->addSql('DROP TABLE symptom_config_symptom_activation_requirement');
        $this->addSql('DROP TABLE symptom_config');
        $this->addSql('DROP TABLE disease_config_symptom_config');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD modifier_strategy VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD priority VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP replace_event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE symptom_activation_requirement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE symptom_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE symptom_activation_requirement (id INT NOT NULL, name VARCHAR(255) NOT NULL, activation_requirement_name VARCHAR(255) NOT NULL, activation_requirement VARCHAR(255) DEFAULT NULL, value INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_204533695e237e06 ON symptom_activation_requirement (name)');
        $this->addSql('CREATE TABLE symptom_config_symptom_activation_requirement (symptom_config_id INT NOT NULL, symptom_activation_requirement_id INT NOT NULL, PRIMARY KEY(symptom_config_id, symptom_activation_requirement_id))');
        $this->addSql('CREATE INDEX idx_63caf87d39ae590 ON symptom_config_symptom_activation_requirement (symptom_activation_requirement_id)');
        $this->addSql('CREATE INDEX idx_63caf879985ac9b ON symptom_config_symptom_activation_requirement (symptom_config_id)');
        $this->addSql('CREATE TABLE symptom_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, symptom_name VARCHAR(255) NOT NULL, trigger VARCHAR(255) DEFAULT NULL, visibility VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_b5486f5c5e237e06 ON symptom_config (name)');
        $this->addSql('CREATE TABLE disease_config_symptom_config (disease_config_id INT NOT NULL, symptom_config_id INT NOT NULL, PRIMARY KEY(disease_config_id, symptom_config_id))');
        $this->addSql('CREATE INDEX idx_99aa55c89985ac9b ON disease_config_symptom_config (symptom_config_id)');
        $this->addSql('CREATE INDEX idx_99aa55c81998f6f9 ON disease_config_symptom_config (disease_config_id)');
        $this->addSql('ALTER TABLE symptom_config_symptom_activation_requirement ADD CONSTRAINT fk_63caf879985ac9b FOREIGN KEY (symptom_config_id) REFERENCES symptom_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE symptom_config_symptom_activation_requirement ADD CONSTRAINT fk_63caf87d39ae590 FOREIGN KEY (symptom_activation_requirement_id) REFERENCES symptom_activation_requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_symptom_config ADD CONSTRAINT fk_99aa55c81998f6f9 FOREIGN KEY (disease_config_id) REFERENCES disease_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_symptom_config ADD CONSTRAINT fk_99aa55c89985ac9b FOREIGN KEY (symptom_config_id) REFERENCES symptom_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD replace_event BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP modifier_strategy');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP priority');
    }
}
