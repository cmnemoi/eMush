<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703160351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_player_bonds (player_source INT NOT NULL, player_target INT NOT NULL, PRIMARY KEY(player_source, player_target))');
        $this->addSql('CREATE INDEX IDX_F30E770EC08AE9AD ON player_player_bonds (player_source)');
        $this->addSql('CREATE INDEX IDX_F30E770ED96FB922 ON player_player_bonds (player_target)');
        $this->addSql('ALTER TABLE player_player_bonds ADD CONSTRAINT FK_F30E770EC08AE9AD FOREIGN KEY (player_source) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_player_bonds ADD CONSTRAINT FK_F30E770ED96FB922 FOREIGN KEY (player_target) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_player_bonds DROP CONSTRAINT FK_F30E770EC08AE9AD');
        $this->addSql('ALTER TABLE player_player_bonds DROP CONSTRAINT FK_F30E770ED96FB922');
        $this->addSql('DROP TABLE player_player_bonds');
    }
}
