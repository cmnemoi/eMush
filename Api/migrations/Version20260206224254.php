<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206224254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE poll_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE poll_option_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE poll_vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE poll (id INT NOT NULL, title VARCHAR(255) NOT NULL, max_vote INT NOT NULL, important BOOLEAN NOT NULL, closed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE poll_option (id INT NOT NULL, poll_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B68343EB3C947C0F ON poll_option (poll_id)');
        $this->addSql('CREATE TABLE poll_vote (id INT NOT NULL, user_id INT DEFAULT NULL, option_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED568EBEA76ED395 ON poll_vote (user_id)');
        $this->addSql('CREATE INDEX IDX_ED568EBEA7C41D6F ON poll_vote (option_id)');
        $this->addSql('ALTER TABLE poll_option ADD CONSTRAINT FK_B68343EB3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA7C41D6F FOREIGN KEY (option_id) REFERENCES poll_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE news ADD poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD399503C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DD399503C947C0F ON news (poll_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news DROP CONSTRAINT FK_1DD399503C947C0F');
        $this->addSql('DROP SEQUENCE poll_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE poll_option_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE poll_vote_id_seq CASCADE');
        $this->addSql('ALTER TABLE poll_option DROP CONSTRAINT FK_B68343EB3C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP CONSTRAINT FK_ED568EBEA76ED395');
        $this->addSql('ALTER TABLE poll_vote DROP CONSTRAINT FK_ED568EBEA7C41D6F');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_option');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP INDEX UNIQ_1DD399503C947C0F');
        $this->addSql('ALTER TABLE news DROP poll_id');
    }
}
