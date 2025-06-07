<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607170826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_player_flirts DROP CONSTRAINT fk_ea49a501c08ae9ad');
        $this->addSql('ALTER TABLE player_player_flirts DROP CONSTRAINT fk_ea49a501d96fb922');
        $this->addSql('DROP TABLE player_player_flirts');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_player_flirts (player_source INT NOT NULL, player_target INT NOT NULL, PRIMARY KEY(player_source, player_target))');
        $this->addSql('CREATE INDEX idx_ea49a501d96fb922 ON player_player_flirts (player_target)');
        $this->addSql('CREATE INDEX idx_ea49a501c08ae9ad ON player_player_flirts (player_source)');
        $this->addSql('ALTER TABLE player_player_flirts ADD CONSTRAINT fk_ea49a501c08ae9ad FOREIGN KEY (player_source) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_player_flirts ADD CONSTRAINT fk_ea49a501d96fb922 FOREIGN KEY (player_target) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
