<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825183333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX unique_channel_participant ON communication_channel_player (channel_id, participant_id)');
        $this->addSql('ALTER TABLE communication_channel ADD version INT NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_channel_participant');
        $this->addSql('ALTER TABLE communication_channel DROP version');
    }
}
