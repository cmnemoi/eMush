<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230514114404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE hunter_config ADD scrap_drop_table TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('ALTER TABLE hunter_config ADD number_of_dropped_scrap TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('ALTER TABLE hunter_config ALTER damage_range SET DEFAULT \'[]\'');
        $this->addSql('COMMENT ON COLUMN hunter_config.scrap_drop_table IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN hunter_config.number_of_dropped_scrap IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE config_difficulty ALTER difficulty_modes DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config DROP scrap_drop_table');
        $this->addSql('ALTER TABLE hunter_config DROP number_of_dropped_scrap');
        $this->addSql('ALTER TABLE hunter_config ALTER damage_range DROP DEFAULT');
    }
}
