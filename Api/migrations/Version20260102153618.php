<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102153618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE npc_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE npc_data (id INT NOT NULL, npc_id INT DEFAULT NULL, memory TEXT DEFAULT \'a:0:{}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4BFB45ACA7D6B89 ON npc_data (npc_id)');
        $this->addSql('COMMENT ON COLUMN npc_data.memory IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE npc_data ADD CONSTRAINT FK_F4BFB45ACA7D6B89 FOREIGN KEY (npc_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE npc_data_id_seq CASCADE');
        $this->addSql('ALTER TABLE npc_data DROP CONSTRAINT FK_F4BFB45ACA7D6B89');
        $this->addSql('DROP TABLE npc_data');
    }
}
