<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422205049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_config ADD is_fireproof BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE equipment_config DROP is_fire_destroyable');
        $this->addSql('ALTER TABLE equipment_config DROP is_fire_breakable');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_config ADD is_fire_breakable BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE equipment_config RENAME COLUMN is_fireproof TO is_fire_destroyable');
    }
}
