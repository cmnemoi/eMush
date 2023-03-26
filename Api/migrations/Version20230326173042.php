<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326173042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hunter_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_hunter_config (game_config_id INT NOT NULL, hunter_config_id INT NOT NULL, PRIMARY KEY(game_config_id, hunter_config_id))');
        $this->addSql('CREATE INDEX IDX_801CD269F67DC781 ON game_config_hunter_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_801CD269324BCEC3 ON game_config_hunter_config (hunter_config_id)');
        $this->addSql('CREATE TABLE hunter_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, hunter_name VARCHAR(255) NOT NULL, initial_health INT NOT NULL, initial_charge INT NOT NULL, initial_armor INT NOT NULL, min_damage INT NOT NULL, max_damage INT NOT NULL, hit_chance INT NOT NULL, dodge_chance INT NOT NULL, draw_cost INT NOT NULL, max_per_wave INT NOT NULL, draw_weight INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5239640E5E237E06 ON hunter_config (name)');
        $this->addSql('ALTER TABLE game_config_hunter_config ADD CONSTRAINT FK_801CD269F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_hunter_config ADD CONSTRAINT FK_801CD269324BCEC3 FOREIGN KEY (hunter_config_id) REFERENCES hunter_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE hunter_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_hunter_config DROP CONSTRAINT FK_801CD269F67DC781');
        $this->addSql('ALTER TABLE game_config_hunter_config DROP CONSTRAINT FK_801CD269324BCEC3');
        $this->addSql('DROP TABLE game_config_hunter_config');
        $this->addSql('DROP TABLE hunter_config');
    }
}
