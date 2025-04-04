<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403195518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_safe_cycles SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE modifier_provider ADD player_disease_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1628790D77C FOREIGN KEY (player_disease_id) REFERENCES disease_player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6709A1628790D77C ON modifier_provider (player_disease_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_6709A1628790D77C');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1628790D77C');
        $this->addSql('ALTER TABLE modifier_provider DROP player_disease_id');
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE config_difficulty ALTER hunter_safe_cycles SET DEFAULT \'[]\'');
    }
}
