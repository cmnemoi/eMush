<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827195517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE commander_mission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE commander_mission (id INT NOT NULL, commander_id INT DEFAULT NULL, subordinate_id INT DEFAULT NULL, mission TEXT DEFAULT \'\' NOT NULL, pending BOOLEAN DEFAULT true NOT NULL, completed BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_41B16E2F3349A583 ON commander_mission (commander_id)');
        $this->addSql('CREATE INDEX IDX_41B16E2F5A373861 ON commander_mission (subordinate_id)');
        $this->addSql('ALTER TABLE commander_mission ADD CONSTRAINT FK_41B16E2F3349A583 FOREIGN KEY (commander_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commander_mission ADD CONSTRAINT FK_41B16E2F5A373861 FOREIGN KEY (subordinate_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX idx_3f6926e899e6f5df');
        $this->addSql('ALTER TABLE player_notification ADD parameters TEXT DEFAULT \'{a:0:{}}\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN player_notification.parameters IS \'(DC2Type:array)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3F6926E899E6F5DF ON player_notification (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE commander_mission_id_seq CASCADE');
        $this->addSql('ALTER TABLE commander_mission DROP CONSTRAINT FK_41B16E2F3349A583');
        $this->addSql('ALTER TABLE commander_mission DROP CONSTRAINT FK_41B16E2F5A373861');
        $this->addSql('DROP TABLE commander_mission');
        $this->addSql('DROP INDEX UNIQ_3F6926E899E6F5DF');
        $this->addSql('ALTER TABLE player_notification DROP parameters');
        $this->addSql('CREATE INDEX idx_3f6926e899e6f5df ON player_notification (player_id)');
    }
}
