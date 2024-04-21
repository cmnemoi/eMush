<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240421125539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_modifier ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT FK_FB26DB166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FB26DB166D1F9C ON game_modifier (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT FK_FB26DB166D1F9C');
        $this->addSql('DROP INDEX IDX_FB26DB166D1F9C');
        $this->addSql('ALTER TABLE game_modifier DROP project_id');
    }
}
