<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829215544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hunter_target_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hunter_target (id INT NOT NULL, daedalus_id INT DEFAULT NULL, patrol_ship_id INT DEFAULT NULL, player_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0DC648E74B5A52D ON hunter_target (daedalus_id)');
        $this->addSql('CREATE INDEX IDX_C0DC648E2034BAC4 ON hunter_target (patrol_ship_id)');
        $this->addSql('CREATE INDEX IDX_C0DC648E99E6F5DF ON hunter_target (player_id)');
        $this->addSql('ALTER TABLE hunter_target ADD CONSTRAINT FK_C0DC648E74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_target ADD CONSTRAINT FK_C0DC648E2034BAC4 FOREIGN KEY (patrol_ship_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_target ADD CONSTRAINT FK_C0DC648E99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter ADD target_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hunter DROP target');
        $this->addSql('ALTER TABLE hunter ADD CONSTRAINT FK_4AD78C65158E0B66 FOREIGN KEY (target_id) REFERENCES hunter_target (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4AD78C65158E0B66 ON hunter (target_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hunter DROP CONSTRAINT FK_4AD78C65158E0B66');
        $this->addSql('DROP SEQUENCE hunter_target_id_seq CASCADE');
        $this->addSql('ALTER TABLE hunter_target DROP CONSTRAINT FK_C0DC648E74B5A52D');
        $this->addSql('ALTER TABLE hunter_target DROP CONSTRAINT FK_C0DC648E2034BAC4');
        $this->addSql('ALTER TABLE hunter_target DROP CONSTRAINT FK_C0DC648E99E6F5DF');
        $this->addSql('DROP TABLE hunter_target');
        $this->addSql('DROP INDEX UNIQ_4AD78C65158E0B66');
        $this->addSql("ALTER TABLE hunter ADD target VARCHAR(255) NOT NULL DEFAULT 'daedalus'");
        $this->addSql('ALTER TABLE hunter DROP target_id');
    }
}
