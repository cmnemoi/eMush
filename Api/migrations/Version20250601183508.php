<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601183508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_planets_found INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_explorations_started INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_ships_destroyed INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_rebel_bases_contacted INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_spores_created INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_statistics_mush_amount INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_planets_found');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_explorations_started');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_ships_destroyed');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_rebel_bases_contacted');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_spores_created');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_statistics_mush_amount');
    }
}
