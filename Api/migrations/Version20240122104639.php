<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240122104639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_player DROP CONSTRAINT fk_f154a5cc74b5a52d');
        $this->addSql('DROP INDEX idx_f154a5cc74b5a52d');
        $this->addSql('ALTER TABLE closed_player RENAME COLUMN daedalus_id TO closed_daedalus_id');
        $this->addSql('ALTER TABLE closed_player ADD CONSTRAINT FK_F154A5CCBBC83F78 FOREIGN KEY (closed_daedalus_id) REFERENCES daedalus_closed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F154A5CCBBC83F78 ON closed_player (closed_daedalus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_player DROP CONSTRAINT FK_F154A5CCBBC83F78');
        $this->addSql('DROP INDEX IDX_F154A5CCBBC83F78');
        $this->addSql('ALTER TABLE closed_player RENAME COLUMN closed_daedalus_id TO daedalus_id');
        $this->addSql('ALTER TABLE closed_player ADD CONSTRAINT fk_f154a5cc74b5a52d FOREIGN KEY (daedalus_id) REFERENCES daedalus_closed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f154a5cc74b5a52d ON closed_player (daedalus_id)');
    }
}
