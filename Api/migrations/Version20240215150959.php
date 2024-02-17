<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215150959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER modifier_strategy SET NOT NULL');
        $this->addSql('ALTER TABLE event_config ADD output_quantity TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE event_config ADD output_table TEXT DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE event_config DROP output_quantity_table');
        $this->addSql('COMMENT ON COLUMN event_config.output_quantity IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN event_config.output_table IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_config ADD output_quantity_table TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_config DROP output_quantity');
        $this->addSql('ALTER TABLE event_config DROP output_table');
        $this->addSql('COMMENT ON COLUMN event_config.output_quantity_table IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE abstract_modifier_config ALTER modifier_strategy DROP NOT NULL');
    }
}
