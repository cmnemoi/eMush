<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123205349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE com_manager_announcement ADD daedalus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE com_manager_announcement ADD CONSTRAINT FK_98F9B71574B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_98F9B71574B5A52D ON com_manager_announcement (daedalus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE com_manager_announcement DROP CONSTRAINT FK_98F9B71574B5A52D');
        $this->addSql('DROP INDEX IDX_98F9B71574B5A52D');
        $this->addSql('ALTER TABLE com_manager_announcement DROP daedalus_id');
    }
}
