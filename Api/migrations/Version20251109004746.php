<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109004746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exploration ADD next_sector_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE exploration ADD CONSTRAINT FK_AC0F0AB3991F1D54 FOREIGN KEY (next_sector_id) REFERENCES planet_sector (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_AC0F0AB3991F1D54 ON exploration (next_sector_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exploration DROP CONSTRAINT FK_AC0F0AB3991F1D54');
        $this->addSql('DROP INDEX IDX_AC0F0AB3991F1D54');
        $this->addSql('ALTER TABLE exploration DROP next_sector_id');
    }
}
