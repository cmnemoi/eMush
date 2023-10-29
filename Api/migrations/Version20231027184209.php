<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027184209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user ADD skins TEXT NOT NULL');
        $this->addSql('ALTER TABLE legacy_user ADD flairs TEXT NOT NULL');
        $this->addSql('ALTER TABLE legacy_user ADD klix TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN legacy_user.skins IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN legacy_user.flairs IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN legacy_user.klix IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user DROP skins');
        $this->addSql('ALTER TABLE legacy_user DROP flairs');
        $this->addSql('ALTER TABLE legacy_user DROP klix');
    }
}
