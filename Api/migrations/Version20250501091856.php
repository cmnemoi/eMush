<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501091856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_a53fc4e45e237e06');
        $this->addSql('ALTER TABLE triumph_config ADD key VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD scope VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD targeted_event VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD target VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD quantity INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD visibility VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD regressive_factor INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD has_compute_strategy BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE triumph_config ADD application_strategies TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('ALTER TABLE triumph_config DROP triumph');
        $this->addSql('ALTER TABLE triumph_config DROP is_all_crew');
        $this->addSql('ALTER TABLE triumph_config DROP team');
        $this->addSql('ALTER TABLE triumph_config ALTER name SET DEFAULT \'\'');
        $this->addSql('COMMENT ON COLUMN triumph_config.application_strategies IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE triumph_config ADD triumph INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE triumph_config ADD is_all_crew BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE triumph_config ADD team VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE triumph_config DROP key');
        $this->addSql('ALTER TABLE triumph_config DROP scope');
        $this->addSql('ALTER TABLE triumph_config DROP targeted_event');
        $this->addSql('ALTER TABLE triumph_config DROP target');
        $this->addSql('ALTER TABLE triumph_config DROP quantity');
        $this->addSql('ALTER TABLE triumph_config DROP visibility');
        $this->addSql('ALTER TABLE triumph_config DROP regressive_factor');
        $this->addSql('ALTER TABLE triumph_config DROP has_compute_strategy');
        $this->addSql('ALTER TABLE triumph_config DROP application_strategies');
        $this->addSql('ALTER TABLE triumph_config ALTER name DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX uniq_a53fc4e45e237e06 ON triumph_config (name)');
    }
}
