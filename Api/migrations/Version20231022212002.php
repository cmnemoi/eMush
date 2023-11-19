<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231022212002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config ADD output_quantity_table TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN event_config.output_quantity_table IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE exploration_log ADD parameters TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN exploration_log.parameters IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config DROP output_quantity_table');
        $this->addSql('ALTER TABLE exploration_log DROP parameters');
    }
}
