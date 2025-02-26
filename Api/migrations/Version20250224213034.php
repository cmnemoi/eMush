<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224213034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ADD rebel_base_contact_duration_min INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE config_daedalus ADD rebel_base_contact_duration_max INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus DROP rebel_base_contact_duration_min');
        $this->addSql('ALTER TABLE config_daedalus DROP rebel_base_contact_duration_max');
    }
}
