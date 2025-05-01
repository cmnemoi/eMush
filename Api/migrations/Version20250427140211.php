<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427140211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_config ADD breakable_type VARCHAR(255) DEFAULT \'none\' NOT NULL');
        $this->addSql('ALTER TABLE equipment_config DROP is_breakable');
        $this->addSql('ALTER TABLE equipment_config DROP destroy_on_break');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_config ADD is_breakable BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE equipment_config ADD destroy_on_break BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE equipment_config DROP breakable_type');
    }
}
