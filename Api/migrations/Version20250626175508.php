<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626175508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_3f6926e899e6f5df');
        $this->addSql('CREATE INDEX IDX_3F6926E899E6F5DF ON player_notification (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_3F6926E899E6F5DF');
        $this->addSql('CREATE UNIQUE INDEX uniq_3f6926e899e6f5df ON player_notification (player_id)');
    }
}
