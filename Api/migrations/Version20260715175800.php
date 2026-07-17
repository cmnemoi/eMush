<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260715175800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moderationsanction ADD author_player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD CONSTRAINT FK_CB19D91B220222F6 FOREIGN KEY (author_player_id) REFERENCES player_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CB19D91B220222F6 ON moderationsanction (author_player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91B220222F6');
        $this->addSql('DROP INDEX IDX_CB19D91B220222F6');
        $this->addSql('ALTER TABLE moderationSanction DROP author_player_id');
    }
}
