<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011175955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE title_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_title_config (game_config_id INT NOT NULL, title_config_id INT NOT NULL, PRIMARY KEY(game_config_id, title_config_id))');
        $this->addSql('CREATE INDEX IDX_3218A073F67DC781 ON game_config_title_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_3218A07385230E48 ON game_config_title_config (title_config_id)');
        $this->addSql('CREATE TABLE title_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, priority TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1A2B3A555E237E06 ON title_config (name)');
        $this->addSql('COMMENT ON COLUMN title_config.priority IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE game_config_title_config ADD CONSTRAINT FK_3218A073F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_title_config ADD CONSTRAINT FK_3218A07385230E48 FOREIGN KEY (title_config_id) REFERENCES title_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player ADD titles TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN player.titles IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE title_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_title_config DROP CONSTRAINT FK_3218A073F67DC781');
        $this->addSql('ALTER TABLE game_config_title_config DROP CONSTRAINT FK_3218A07385230E48');
        $this->addSql('DROP TABLE game_config_title_config');
        $this->addSql('DROP TABLE title_config');
        $this->addSql('ALTER TABLE player DROP titles');
    }
}
