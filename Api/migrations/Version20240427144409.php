<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240427144409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_game_modifier DROP CONSTRAINT fk_d46ab1dd166d1f9c');
        $this->addSql('ALTER TABLE project_game_modifier DROP CONSTRAINT fk_d46ab1dde2cb751');
        $this->addSql('DROP TABLE project_game_modifier');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_game_modifier (project_id INT NOT NULL, game_modifier_id INT NOT NULL, PRIMARY KEY(project_id, game_modifier_id))');
        $this->addSql('CREATE INDEX idx_d46ab1dde2cb751 ON project_game_modifier (game_modifier_id)');
        $this->addSql('CREATE INDEX idx_d46ab1dd166d1f9c ON project_game_modifier (project_id)');
        $this->addSql('ALTER TABLE project_game_modifier ADD CONSTRAINT fk_d46ab1dd166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_game_modifier ADD CONSTRAINT fk_d46ab1dde2cb751 FOREIGN KEY (game_modifier_id) REFERENCES game_modifier (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
