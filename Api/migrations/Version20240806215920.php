<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240806215920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_config_skill_config (game_config_id INT NOT NULL, skill_config_id INT NOT NULL, PRIMARY KEY(game_config_id, skill_config_id))');
        $this->addSql('CREATE INDEX IDX_D3F3E14EF67DC781 ON game_config_skill_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_D3F3E14E772A3DDE ON game_config_skill_config (skill_config_id)');
        $this->addSql('ALTER TABLE game_config_skill_config ADD CONSTRAINT FK_D3F3E14EF67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_skill_config ADD CONSTRAINT FK_D3F3E14E772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_fbc07b68d7e86f28 RENAME TO IDX_FBC07B68B9E73F9C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_config_skill_config DROP CONSTRAINT FK_D3F3E14EF67DC781');
        $this->addSql('ALTER TABLE game_config_skill_config DROP CONSTRAINT FK_D3F3E14E772A3DDE');
        $this->addSql('DROP TABLE game_config_skill_config');
        $this->addSql('ALTER INDEX idx_fbc07b68b9e73f9c RENAME TO idx_fbc07b68d7e86f28');
    }
}
