<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114145051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_difficulty ADD random_spores TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN config_difficulty.random_spores IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE config_game ADD special_options TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN config_game.special_options IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_game DROP special_options');
        $this->addSql('ALTER TABLE config_difficulty DROP random_spores');
    }
}
