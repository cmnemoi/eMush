<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504081143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_player (project_id INT NOT NULL, player_id INT NOT NULL, PRIMARY KEY(project_id, player_id))');
        $this->addSql('CREATE INDEX IDX_8FBD912F166D1F9C ON project_player (project_id)');
        $this->addSql('CREATE INDEX IDX_8FBD912F99E6F5DF ON project_player (player_id)');
        $this->addSql('ALTER TABLE project_player ADD CONSTRAINT FK_8FBD912F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_player ADD CONSTRAINT FK_8FBD912F99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_target DROP CONSTRAINT fk_fb2587b1166d1f9c');
        $this->addSql('DROP INDEX idx_fb2587b1166d1f9c');
        $this->addSql('ALTER TABLE status_target DROP project_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_player DROP CONSTRAINT FK_8FBD912F166D1F9C');
        $this->addSql('ALTER TABLE project_player DROP CONSTRAINT FK_8FBD912F99E6F5DF');
        $this->addSql('DROP TABLE project_player');
        $this->addSql('ALTER TABLE status_target ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status_target ADD CONSTRAINT fk_fb2587b1166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fb2587b1166d1f9c ON status_target (project_id)');
    }
}
