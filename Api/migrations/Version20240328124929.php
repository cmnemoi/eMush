<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240328124929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_log_readers (room_log_id INT NOT NULL, player_id INT NOT NULL, PRIMARY KEY(room_log_id, player_id))');
        $this->addSql('CREATE INDEX IDX_18551AADAB299B47 ON room_log_readers (room_log_id)');
        $this->addSql('CREATE INDEX IDX_18551AAD99E6F5DF ON room_log_readers (player_id)');
        $this->addSql('ALTER TABLE room_log_readers ADD CONSTRAINT FK_18551AADAB299B47 FOREIGN KEY (room_log_id) REFERENCES room_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE room_log_readers ADD CONSTRAINT FK_18551AAD99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE room_log ADD timestampable_canceled BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_log_readers DROP CONSTRAINT FK_18551AADAB299B47');
        $this->addSql('ALTER TABLE room_log_readers DROP CONSTRAINT FK_18551AAD99E6F5DF');
        $this->addSql('DROP TABLE room_log_readers');
        $this->addSql('ALTER TABLE room_log DROP timestampable_canceled');
    }
}
