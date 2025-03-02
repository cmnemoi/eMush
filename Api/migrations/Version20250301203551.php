<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301203551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE xyloph_config ALTER quantity SET DEFAULT -1');
        $this->addSql('ALTER TABLE xyloph_config ALTER key SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE xyloph_config ALTER weight SET DEFAULT 0');
        $this->addSql('ALTER INDEX idx_fc5cf88bfbdb82ce RENAME TO IDX_902FB4C5FBDB82CE');
        $this->addSql('ALTER INDEX idx_fc5cf88b74b5a52d RENAME TO IDX_902FB4C574B5A52D');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_902fb4c574b5a52d RENAME TO idx_fc5cf88b74b5a52d');
        $this->addSql('ALTER INDEX idx_902fb4c5fbdb82ce RENAME TO idx_fc5cf88bfbdb82ce');
        $this->addSql('ALTER TABLE xyloph_config DROP quantity');
        $this->addSql('ALTER TABLE xyloph_config ALTER key DROP DEFAULT');
        $this->addSql('ALTER TABLE xyloph_config ALTER weight DROP DEFAULT');
    }
}
