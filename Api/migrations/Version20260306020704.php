<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306020704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create personal_notes and personal_notes_tab, then backfill existing players';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE personal_notes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE personal_notes_tab_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE personal_notes (id INT NOT NULL, player_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5942E09A99E6F5DF ON personal_notes (player_id)');
        $this->addSql('CREATE TABLE personal_notes_tab (id INT NOT NULL, personal_notes_id INT NOT NULL, index INT NOT NULL, icon VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT \'\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_956F4F4150692B1F ON personal_notes_tab (personal_notes_id)');
        $this->addSql('ALTER TABLE personal_notes ADD CONSTRAINT FK_5942E09A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personal_notes_tab ADD CONSTRAINT FK_956F4F4150692B1F FOREIGN KEY (personal_notes_id) REFERENCES personal_notes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Create a PersonalNote for each existing Player
        $this->addSql("
            INSERT INTO personal_notes (id, player_id, created_at, updated_at)
            SELECT
                nextval('personal_notes_id_seq'),
                id,
                NOW(),
                NOW()
            FROM player
            WHERE NOT EXISTS (
                SELECT 1 FROM personal_notes WHERE personal_notes.player_id = player.id
            )
        ");
        // Create a PersonalNoteTab for the new PersonalNote
        $this->addSql("
            INSERT INTO personal_notes_tab (id, personal_notes_id, index, icon, content, created_at, updated_at)
            SELECT
                nextval('personal_notes_tab_id_seq'),
                pn.id,
                15,
                NULL,
                '',
                NOW(),
                NOW()
            FROM personal_notes pn
            WHERE NOT EXISTS (
                SELECT 1 FROM personal_notes_tab WHERE personal_notes_tab.personal_notes_id = pn.id
            )
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personal_notes_tab DROP CONSTRAINT FK_956F4F4150692B1F');
        $this->addSql('ALTER TABLE personal_notes DROP CONSTRAINT FK_5942E09A99E6F5DF');
        $this->addSql('DROP TABLE personal_notes_tab');
        $this->addSql('DROP TABLE personal_notes');
        $this->addSql('DROP SEQUENCE personal_notes_tab_id_seq');
        $this->addSql('DROP SEQUENCE personal_notes_id_seq');
    }
}
