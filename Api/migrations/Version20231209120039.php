<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231209120039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus ADD exploration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760ACB0970CE FOREIGN KEY (exploration_id) REFERENCES exploration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71DA760ACB0970CE ON daedalus (exploration_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus DROP CONSTRAINT FK_71DA760ACB0970CE');
        $this->addSql('DROP INDEX UNIQ_71DA760ACB0970CE');
        $this->addSql('ALTER TABLE daedalus DROP exploration_id');
    }
}
