<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230401223237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_info ADD daily_action_points_spent INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD number_of_hunter_killed INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD number_of_mushs INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ADD init_hunter_points INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config_difficulty ADD hunter_spawn_rate INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE daedalus_info DROP daily_action_points_spent');
        $this->addSql('ALTER TABLE daedalus_info DROP number_of_hunter_killed');
        $this->addSql('ALTER TABLE daedalus_info DROP number_of_mushs');
        $this->addSql('ALTER TABLE config_daedalus DROP init_hunter_points');
        $this->addSql('ALTER TABLE config_difficulty DROP hunter_spawn_rate');
    }
}
