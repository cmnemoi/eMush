<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240327211456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message_readers (message_id INT NOT NULL, player_id INT NOT NULL, PRIMARY KEY(message_id, player_id))');
        $this->addSql('CREATE INDEX IDX_A425B609537A1329 ON message_readers (message_id)');
        $this->addSql('CREATE INDEX IDX_A425B60999E6F5DF ON message_readers (player_id)');
        $this->addSql('ALTER TABLE message_readers ADD CONSTRAINT FK_A425B609537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_readers ADD CONSTRAINT FK_A425B60999E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_readers DROP CONSTRAINT FK_A425B609537A1329');
        $this->addSql('ALTER TABLE message_readers DROP CONSTRAINT FK_A425B60999E6F5DF');
        $this->addSql('DROP TABLE message_readers');
    }
}
