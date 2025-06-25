<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625232158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_skill_config (player_id INT NOT NULL, skill_config_id INT NOT NULL, PRIMARY KEY(player_id, skill_config_id))');
        $this->addSql('CREATE INDEX IDX_7BFD2B4A99E6F5DF ON player_skill_config (player_id)');
        $this->addSql('CREATE INDEX IDX_7BFD2B4A772A3DDE ON player_skill_config (skill_config_id)');
        $this->addSql('ALTER TABLE player_skill_config ADD CONSTRAINT FK_7BFD2B4A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_skill_config ADD CONSTRAINT FK_7BFD2B4A772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player_skill_config DROP CONSTRAINT FK_7BFD2B4A99E6F5DF');
        $this->addSql('ALTER TABLE player_skill_config DROP CONSTRAINT FK_7BFD2B4A772A3DDE');
        $this->addSql('DROP TABLE player_skill_config');
    }
}
