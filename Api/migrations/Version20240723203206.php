<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723203206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE skill_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE skill_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE character_config_skill_config (character_config_id INT NOT NULL, skill_config_id INT NOT NULL, PRIMARY KEY(character_config_id, skill_config_id))');
        $this->addSql('CREATE INDEX IDX_6A122E00A38BA4B8 ON character_config_skill_config (character_config_id)');
        $this->addSql('CREATE INDEX IDX_6A122E00772A3DDE ON character_config_skill_config (skill_config_id)');
        $this->addSql('CREATE TABLE skill (id INT NOT NULL, skill_config_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E3DE477772A3DDE ON skill (skill_config_id)');
        $this->addSql('CREATE TABLE skill_config (id INT NOT NULL, spawn_equipment_config_id INT DEFAULT NULL, specialist_points_config_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FBC07B68F5D9D02 ON skill_config (spawn_equipment_config_id)');
        $this->addSql('CREATE INDEX IDX_FBC07B68D7E86F28 ON skill_config (specialist_points_config_id)');
        $this->addSql('CREATE TABLE skill_config_abstract_modifier_config (skill_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(skill_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_B4A9589E772A3DDE ON skill_config_abstract_modifier_config (skill_config_id)');
        $this->addSql('CREATE INDEX IDX_B4A9589EBFA8DC8C ON skill_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('CREATE TABLE skill_config_action_config (skill_config_id INT NOT NULL, action_config_id INT NOT NULL, PRIMARY KEY(skill_config_id, action_config_id))');
        $this->addSql('CREATE INDEX IDX_A1153439772A3DDE ON skill_config_action_config (skill_config_id)');
        $this->addSql('CREATE INDEX IDX_A115343980DD159E ON skill_config_action_config (action_config_id)');
        $this->addSql('ALTER TABLE character_config_skill_config ADD CONSTRAINT FK_6A122E00A38BA4B8 FOREIGN KEY (character_config_id) REFERENCES character_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_skill_config ADD CONSTRAINT FK_6A122E00772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config ADD CONSTRAINT FK_FBC07B68F5D9D02 FOREIGN KEY (spawn_equipment_config_id) REFERENCES spawn_equipment_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config ADD CONSTRAINT FK_FBC07B68D7E86F28 FOREIGN KEY (specialist_points_config_id) REFERENCES status_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config_abstract_modifier_config ADD CONSTRAINT FK_B4A9589E772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config_abstract_modifier_config ADD CONSTRAINT FK_B4A9589EBFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config_action_config ADD CONSTRAINT FK_A1153439772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config_action_config ADD CONSTRAINT FK_A115343980DD159E FOREIGN KEY (action_config_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config DROP skills');
        $this->addSql('ALTER TABLE player ADD skills_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player DROP skills');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A657FF61858 FOREIGN KEY (skills_id) REFERENCES skill (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A657FF61858 ON player (skills_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player DROP CONSTRAINT FK_98197A657FF61858');
        $this->addSql('DROP SEQUENCE skill_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE skill_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE character_config_skill_config DROP CONSTRAINT FK_6A122E00A38BA4B8');
        $this->addSql('ALTER TABLE character_config_skill_config DROP CONSTRAINT FK_6A122E00772A3DDE');
        $this->addSql('ALTER TABLE skill DROP CONSTRAINT FK_5E3DE477772A3DDE');
        $this->addSql('ALTER TABLE skill_config DROP CONSTRAINT FK_FBC07B68F5D9D02');
        $this->addSql('ALTER TABLE skill_config DROP CONSTRAINT FK_FBC07B68D7E86F28');
        $this->addSql('ALTER TABLE skill_config_abstract_modifier_config DROP CONSTRAINT FK_B4A9589E772A3DDE');
        $this->addSql('ALTER TABLE skill_config_abstract_modifier_config DROP CONSTRAINT FK_B4A9589EBFA8DC8C');
        $this->addSql('ALTER TABLE skill_config_action_config DROP CONSTRAINT FK_A1153439772A3DDE');
        $this->addSql('ALTER TABLE skill_config_action_config DROP CONSTRAINT FK_A115343980DD159E');
        $this->addSql('DROP TABLE character_config_skill_config');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_config');
        $this->addSql('DROP TABLE skill_config_abstract_modifier_config');
        $this->addSql('DROP TABLE skill_config_action_config');
        $this->addSql('ALTER TABLE character_config ADD skills TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN character_config.skills IS \'(DC2Type:array)\'');
        $this->addSql('DROP INDEX UNIQ_98197A657FF61858');
        $this->addSql('ALTER TABLE player ADD skills TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE player DROP skills_id');
        $this->addSql('COMMENT ON COLUMN player.skills IS \'(DC2Type:array)\'');
    }
}
