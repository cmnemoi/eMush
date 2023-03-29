<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330110721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hunter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE hunter_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_hunter_config (game_config_id INT NOT NULL, hunter_config_id INT NOT NULL, PRIMARY KEY(game_config_id, hunter_config_id))');
        $this->addSql('CREATE INDEX IDX_801CD269F67DC781 ON game_config_hunter_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_801CD269324BCEC3 ON game_config_hunter_config (hunter_config_id)');
        $this->addSql('CREATE TABLE hunter (id INT NOT NULL, hunter_config_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, hunter_variables_id INT DEFAULT NULL, target VARCHAR(255) NOT NULL, in_pool BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4AD78C65324BCEC3 ON hunter (hunter_config_id)');
        $this->addSql('CREATE INDEX IDX_4AD78C6574B5A52D ON hunter (daedalus_id)');
        $this->addSql('CREATE TABLE hunter_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, hunter_name VARCHAR(255) NOT NULL, initial_health INT NOT NULL, initial_armor INT NOT NULL, damage_range TEXT NOT NULL, hit_chance INT NOT NULL, dodge_chance INT NOT NULL, draw_cost INT NOT NULL, max_per_wave INT DEFAULT NULL, draw_weight INT NOT NULL, spawn_difficulty INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5239640E5E237E06 ON hunter_config (name)');
        $this->addSql('COMMENT ON COLUMN hunter_config.damage_range IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE hunter_config_status_config (hunter_config_id INT NOT NULL, status_config_id INT NOT NULL, PRIMARY KEY(hunter_config_id, status_config_id))');
        $this->addSql('CREATE INDEX IDX_6A134A27324BCEC3 ON hunter_config_status_config (hunter_config_id)');
        $this->addSql('CREATE INDEX IDX_6A134A27AC4E86C2 ON hunter_config_status_config (status_config_id)');
        $this->addSql('ALTER TABLE game_config_hunter_config ADD CONSTRAINT FK_801CD269F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_hunter_config ADD CONSTRAINT FK_801CD269324BCEC3 FOREIGN KEY (hunter_config_id) REFERENCES hunter_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter ADD CONSTRAINT FK_4AD78C65324BCEC3 FOREIGN KEY (hunter_config_id) REFERENCES hunter_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter ADD CONSTRAINT FK_4AD78C6574B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter ADD CONSTRAINT FK_4AD78C656E3453A8 FOREIGN KEY (hunter_variables_id) REFERENCES game_variable_collection (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_config_status_config ADD CONSTRAINT FK_6A134A27324BCEC3 FOREIGN KEY (hunter_config_id) REFERENCES hunter_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_config_status_config ADD CONSTRAINT FK_6A134A27AC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE config_difficulty ADD difficulty_modes TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN config_difficulty.difficulty_modes IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE status_target ADD hunter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status_target ADD CONSTRAINT FK_FB2587B1A7DC5C81 FOREIGN KEY (hunter_id) REFERENCES hunter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FB2587B1A7DC5C81 ON status_target (hunter_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status_target DROP CONSTRAINT FK_FB2587B1A7DC5C81');
        $this->addSql('DROP SEQUENCE hunter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE hunter_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_hunter_config DROP CONSTRAINT FK_801CD269F67DC781');
        $this->addSql('ALTER TABLE game_config_hunter_config DROP CONSTRAINT FK_801CD269324BCEC3');
        $this->addSql('ALTER TABLE hunter DROP CONSTRAINT FK_4AD78C65324BCEC3');
        $this->addSql('ALTER TABLE hunter DROP CONSTRAINT FK_4AD78C6574B5A52D');
        $this->addSql('ALTER TABLE hunter DROP CONSTRAINT FK_4AD78C656E3453A8');
        $this->addSql('ALTER TABLE hunter_config_status_config DROP CONSTRAINT FK_6A134A27324BCEC3');
        $this->addSql('ALTER TABLE hunter_config_status_config DROP CONSTRAINT FK_6A134A27AC4E86C2');
        $this->addSql('DROP TABLE game_config_hunter_config');
        $this->addSql('DROP TABLE hunter');
        $this->addSql('DROP TABLE hunter_config');
        $this->addSql('DROP TABLE hunter_config_status_config');
        $this->addSql('DROP INDEX IDX_FB2587B1A7DC5C81');
        $this->addSql('ALTER TABLE status_target DROP hunter_id');
        $this->addSql('ALTER TABLE config_difficulty DROP difficulty_modes');
    }
}
