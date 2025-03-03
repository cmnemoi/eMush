<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303183928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modifier_provider ADD xyloph_entry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162DA5DC7EE FOREIGN KEY (xyloph_entry_id) REFERENCES xyloph_entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6709A162DA5DC7EE ON modifier_provider (xyloph_entry_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162DA5DC7EE');
        $this->addSql('DROP INDEX IDX_6709A162DA5DC7EE');
        $this->addSql('ALTER TABLE modifier_provider DROP xyloph_entry_id');
    }
}
