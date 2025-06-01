<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601151635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE triumph_config ALTER target SET DEFAULT \'none\'');
        $this->addSql('ALTER TABLE triumph_config RENAME COLUMN target TO target_setting');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE triumph_config RENAME COLUMN target_setting TO target');
        $this->addSql('ALTER TABLE triumph_config ALTER target SET DEFAULT \'\'');
    }
}
