<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027151532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user ALTER history_heroes TYPE TEXT');
        $this->addSql('ALTER TABLE legacy_user ALTER history_ships TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN legacy_user.history_heroes IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN legacy_user.history_ships IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user ALTER history_heroes TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE legacy_user ALTER history_ships TYPE VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN legacy_user.history_heroes IS NULL');
        $this->addSql('COMMENT ON COLUMN legacy_user.history_ships IS NULL');
    }
}
