<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812212707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE player_notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE player_notification (id INT NOT NULL, player_id INT DEFAULT NULL, message VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F6926E899E6F5DF ON player_notification (player_id)');
        $this->addSql('ALTER TABLE player_notification ADD CONSTRAINT FK_3F6926E899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE player_notification_id_seq CASCADE');
        $this->addSql('ALTER TABLE player_notification DROP CONSTRAINT FK_3F6926E899E6F5DF');
        $this->addSql('DROP TABLE player_notification');
    }
}
