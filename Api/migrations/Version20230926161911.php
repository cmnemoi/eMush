<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926161911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_target ADD daedalus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status_target ADD CONSTRAINT FK_FB2587B174B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FB2587B174B5A52D ON status_target (daedalus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status_target DROP CONSTRAINT FK_FB2587B174B5A52D');
        $this->addSql('DROP INDEX IDX_FB2587B174B5A52D');
        $this->addSql('ALTER TABLE status_target DROP daedalus_id');
    }
}
