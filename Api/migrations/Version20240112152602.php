<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240112152602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE closed_exploration_closed_player (closed_exploration_id INT NOT NULL, closed_player_id INT NOT NULL, PRIMARY KEY(closed_exploration_id, closed_player_id))');
        $this->addSql('CREATE INDEX IDX_BC42A200464F34B ON closed_exploration_closed_player (closed_exploration_id)');
        $this->addSql('CREATE INDEX IDX_BC42A200418277A4 ON closed_exploration_closed_player (closed_player_id)');
        $this->addSql('ALTER TABLE closed_exploration_closed_player ADD CONSTRAINT FK_BC42A200464F34B FOREIGN KEY (closed_exploration_id) REFERENCES closed_exploration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE closed_exploration_closed_player ADD CONSTRAINT FK_BC42A200418277A4 FOREIGN KEY (closed_player_id) REFERENCES closed_player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE closed_exploration DROP explorator_names');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_exploration_closed_player DROP CONSTRAINT FK_BC42A200464F34B');
        $this->addSql('ALTER TABLE closed_exploration_closed_player DROP CONSTRAINT FK_BC42A200418277A4');
        $this->addSql('DROP TABLE closed_exploration_closed_player');
        $this->addSql('ALTER TABLE closed_exploration ADD explorator_names TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN closed_exploration.explorator_names IS \'(DC2Type:array)\'');
    }
}
