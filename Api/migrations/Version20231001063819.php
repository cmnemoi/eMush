<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231001063819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status RENAME COLUMN charge TO charge_id');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C55284914 FOREIGN KEY (charge_id) REFERENCES game_variable_collection (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7B00651C55284914 ON status (charge_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C55284914');
        $this->addSql('DROP INDEX IDX_7B00651C55284914');
        $this->addSql('ALTER TABLE status RENAME COLUMN charge_id TO charge');
    }
}
