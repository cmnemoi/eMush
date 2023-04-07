<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230407081500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ADD init_hunter_points INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ADD hunter_spawn_rate INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ADD hunter_safe_cycles TEXT NOT NULL DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE config_difficulty ADD starting_hunters_number_of_truce_cycles INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN config_difficulty.hunter_safe_cycles IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE daedalus ADD daily_action_points_spent INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE config_daedalus DROP init_hunter_points');
        $this->addSql('ALTER TABLE daedalus DROP daily_action_points_spent');
        $this->addSql('ALTER TABLE config_difficulty DROP hunter_spawn_rate');
        $this->addSql('ALTER TABLE config_difficulty DROP hunter_safe_cycles');
        $this->addSql('ALTER TABLE config_difficulty DROP starting_hunters_number_of_truce_cycles');
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes SET DEFAULT \'[]\'');
    }
}
