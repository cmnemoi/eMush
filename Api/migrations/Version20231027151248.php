<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027151248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user DROP CONSTRAINT FK_A2236D5116C70FC6');
        $this->addSql('ALTER TABLE legacy_user ADD CONSTRAINT FK_A2236D5116C70FC6 FOREIGN KEY (twinoid_profile_id) REFERENCES legacy_user_twinoid_profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE legacy_user DROP CONSTRAINT fk_a2236d5116c70fc6');
        $this->addSql('ALTER TABLE legacy_user ADD CONSTRAINT fk_a2236d5116c70fc6 FOREIGN KEY (twinoid_profile_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
