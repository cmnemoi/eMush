<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605173848 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE sanction_evidence_id_seq CASCADE');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50537A1329');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50418277A4');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F50AB299B47');
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F5099D1ACCF');
        $this->addSql('DROP TABLE sanction_evidence');
    }
}
