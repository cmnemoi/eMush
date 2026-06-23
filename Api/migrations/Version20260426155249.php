<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426155249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config ADD tags TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE event_config ADD fight_strength INT DEFAULT 0');
        $this->addSql('ALTER TABLE event_config DROP tag');
        $this->addSql('COMMENT ON COLUMN event_config.tags IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config ADD tag VARCHAR(255) DEFAULT \'neutral\'');
        $this->addSql('ALTER TABLE event_config DROP tags');
        $this->addSql('ALTER TABLE event_config DROP fight_strength');
    }
}
