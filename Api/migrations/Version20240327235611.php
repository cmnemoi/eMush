<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240327235611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message_favorites (message_id INT NOT NULL, player_id INT NOT NULL, PRIMARY KEY(message_id, player_id))');
        $this->addSql('CREATE INDEX IDX_8ED066C3537A1329 ON message_favorites (message_id)');
        $this->addSql('CREATE INDEX IDX_8ED066C399E6F5DF ON message_favorites (player_id)');
        $this->addSql('ALTER TABLE message_favorites ADD CONSTRAINT FK_8ED066C3537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_favorites ADD CONSTRAINT FK_8ED066C399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_favorites DROP CONSTRAINT FK_8ED066C3537A1329');
        $this->addSql('ALTER TABLE message_favorites DROP CONSTRAINT FK_8ED066C399E6F5DF');
        $this->addSql('DROP TABLE message_favorites');
    }
}
