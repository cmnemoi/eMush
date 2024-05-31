<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240531205330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moderationsanction ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD CONSTRAINT FK_CB19D91B99E6F5DF FOREIGN KEY (player_id) REFERENCES player_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderationsanction ADD CONSTRAINT FK_CB19D91BF675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CB19D91B99E6F5DF ON moderationsanction (player_id)');
        $this->addSql('CREATE INDEX IDX_CB19D91BF675F31B ON moderationsanction (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91B99E6F5DF');
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91BF675F31B');
        $this->addSql('DROP INDEX IDX_CB19D91B99E6F5DF');
        $this->addSql('DROP INDEX IDX_CB19D91BF675F31B');
        $this->addSql('ALTER TABLE moderationSanction DROP player_id');
        $this->addSql('ALTER TABLE moderationSanction DROP author_id');
    }
}
