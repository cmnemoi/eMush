<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511111740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE drone_info_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE drone_info (id INT NOT NULL, drone_id INT DEFAULT NULL, nick_name INT DEFAULT 0 NOT NULL, serial_number INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3F268A382CDF9A ON drone_info (drone_id)');
        $this->addSql('ALTER TABLE drone_info ADD CONSTRAINT FK_3F268A382CDF9A FOREIGN KEY (drone_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_equipment DROP nick_name');
        $this->addSql('ALTER TABLE game_equipment DROP serial_number');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE drone_info_id_seq CASCADE');
        $this->addSql('ALTER TABLE drone_info DROP CONSTRAINT FK_3F268A382CDF9A');
        $this->addSql('DROP TABLE drone_info');
        $this->addSql('ALTER TABLE game_equipment ADD nick_name INT DEFAULT 0');
        $this->addSql('ALTER TABLE game_equipment ADD serial_number INT DEFAULT 0');
    }
}
