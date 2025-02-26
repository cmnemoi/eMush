<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223114835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_68078bfa38b52ec7');
        $this->addSql('ALTER TABLE config_daedalus ADD number_of_cycles_before_next_rebel_base_contact INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE rebel_base ADD contact_start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE rebel_base ADD contact_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE rebel_base DROP is_contacting');
        $this->addSql('COMMENT ON COLUMN rebel_base.contact_start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN rebel_base.contact_end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE rebel_base_config ADD contact_order INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus DROP number_of_cycles_before_next_rebel_base_contact');
        $this->addSql('ALTER TABLE rebel_base ADD is_contacting BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE rebel_base DROP contact_start_date');
        $this->addSql('ALTER TABLE rebel_base DROP contact_end_date');
        $this->addSql('ALTER TABLE rebel_base_config DROP contact_order');
    }
}
