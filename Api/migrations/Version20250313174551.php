<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313174551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ALTER chaola_toggle TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE config_daedalus ALTER chaola_toggle SET DEFAULT \'finola_chao\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ALTER chaola_toggle TYPE BOOLEAN');
        $this->addSql('ALTER TABLE config_daedalus ALTER chaola_toggle SET DEFAULT false');
    }
}
