<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208200544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE link_with_sol_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE link_with_sol (id INT NOT NULL, daedalus_id INT DEFAULT NULL, strength INT DEFAULT 0 NOT NULL, is_established BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A7ACEA274B5A52D ON link_with_sol (daedalus_id)');
        $this->addSql('ALTER TABLE link_with_sol ADD CONSTRAINT FK_4A7ACEA274B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE link_with_sol_id_seq CASCADE');
        $this->addSql('ALTER TABLE link_with_sol DROP CONSTRAINT FK_4A7ACEA274B5A52D');
        $this->addSql('DROP TABLE link_with_sol');
    }
}
