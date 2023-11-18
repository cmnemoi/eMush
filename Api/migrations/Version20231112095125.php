<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231112095125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_exploration ADD explored_sector_keys TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN closed_exploration.explored_sector_keys IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE exploration ADD cycle INT NOT NULL');
        $this->addSql('ALTER TABLE exploration ADD is_changing_cycle BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exploration DROP cycle');
        $this->addSql('ALTER TABLE exploration DROP is_changing_cycle');
        $this->addSql('ALTER TABLE closed_exploration DROP explored_sector_keys');
    }
}
