<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240714100701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sanction_evidence_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sanction_evidence (id INT NOT NULL, message_id INT DEFAULT NULL, closed_player_id INT DEFAULT NULL, room_log_id INT DEFAULT NULL, moderation_sanction_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_31059F50537A1329 ON sanction_evidence (message_id)');
        $this->addSql('CREATE INDEX IDX_31059F50418277A4 ON sanction_evidence (closed_player_id)');
        $this->addSql('CREATE INDEX IDX_31059F50AB299B47 ON sanction_evidence (room_log_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31059F5099D1ACCF ON sanction_evidence (moderation_sanction_id)');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F50537A1329 FOREIGN KEY (message_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F50418277A4 FOREIGN KEY (closed_player_id) REFERENCES closed_player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F50AB299B47 FOREIGN KEY (room_log_id) REFERENCES room_log (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F5099D1ACCF FOREIGN KEY (moderation_sanction_id) REFERENCES moderationSanction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD day INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE message ADD cycle INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE moderationsanction ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderationsanction ADD CONSTRAINT FK_CB19D91B99E6F5DF FOREIGN KEY (player_id) REFERENCES player_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE moderationsanction ADD CONSTRAINT FK_CB19D91BF675F31B FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CB19D91B99E6F5DF ON moderationsanction (player_id)');
        $this->addSql('CREATE INDEX IDX_CB19D91BF675F31B ON moderationsanction (author_id)');
        $this->addSql('ALTER TABLE room_log DROP date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE sanction_evidence_id_seq CASCADE');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50537A1329');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50418277A4');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50AB299B47');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F5099D1ACCF');
        $this->addSql('DROP TABLE sanction_evidence');
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91B99E6F5DF');
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91BF675F31B');
        $this->addSql('DROP INDEX IDX_CB19D91B99E6F5DF');
        $this->addSql('DROP INDEX IDX_CB19D91BF675F31B');
        $this->addSql('ALTER TABLE moderationSanction DROP player_id');
        $this->addSql('ALTER TABLE moderationSanction DROP author_id');
        $this->addSql('ALTER TABLE room_log ADD date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE message DROP day');
        $this->addSql('ALTER TABLE message DROP cycle');
    }
}
