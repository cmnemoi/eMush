<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419155240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_config_project_config (game_config_id INT NOT NULL, project_config_id INT NOT NULL, PRIMARY KEY(game_config_id, project_config_id))');
        $this->addSql('CREATE INDEX IDX_102A157BF67DC781 ON game_config_project_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_102A157B35E74354 ON game_config_project_config (project_config_id)');
        $this->addSql('ALTER TABLE game_config_project_config ADD CONSTRAINT FK_102A157BF67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_project_config ADD CONSTRAINT FK_102A157B35E74354 FOREIGN KEY (project_config_id) REFERENCES project_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_config_project_config DROP CONSTRAINT FK_102A157BF67DC781');
        $this->addSql('ALTER TABLE game_config_project_config DROP CONSTRAINT FK_102A157B35E74354');
        $this->addSql('DROP TABLE game_config_project_config');
    }
}
