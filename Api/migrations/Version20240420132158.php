<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420132158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_target ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status_target ADD CONSTRAINT FK_FB2587B1166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FB2587B1166D1F9C ON status_target (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_target DROP CONSTRAINT FK_FB2587B1166D1F9C');
        $this->addSql('DROP INDEX IDX_FB2587B1166D1F9C');
        $this->addSql('ALTER TABLE status_target DROP project_id');
    }
}
