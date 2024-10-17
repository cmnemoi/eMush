<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241017070848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_log ALTER place SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE room_log ALTER visibility SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE room_log ALTER log SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE room_log ALTER parameters SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE room_log ALTER type SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE room_log ALTER day SET DEFAULT 0');
        $this->addSql('ALTER TABLE room_log ALTER cycle SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_log ALTER place DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER visibility DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER log DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER parameters DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER day DROP DEFAULT');
        $this->addSql('ALTER TABLE room_log ALTER cycle DROP DEFAULT');
    }
}
