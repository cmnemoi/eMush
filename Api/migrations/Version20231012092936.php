<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231012092936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE planet_name_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE planet_name (id INT NOT NULL, prefix INT DEFAULT NULL, first_syllable INT NOT NULL, second_syllable INT DEFAULT NULL, third_syllable INT DEFAULT NULL, fourth_syllable INT NOT NULL, fifth_syllable INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE planet ADD name_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet DROP name');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA571179CD6 FOREIGN KEY (name_id) REFERENCES planet_name (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_68136AA571179CD6 ON planet (name_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE planet DROP CONSTRAINT FK_68136AA571179CD6');
        $this->addSql('DROP SEQUENCE planet_name_id_seq CASCADE');
        $this->addSql('DROP TABLE planet_name');
        $this->addSql('DROP INDEX IDX_68136AA571179CD6');
        $this->addSql('ALTER TABLE planet ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE planet DROP name_id');
    }
}
