<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020212119 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_fruit (id INT AUTO_INCREMENT NOT NULL, daedalus_id INT DEFAULT NULL, game_plant_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, action_point INT NOT NULL, health_point INT NOT NULL, moral_point VARCHAR(255) NOT NULL, satiety INT NOT NULL, cures LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', diseases LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_9238AB1874B5A52D (daedalus_id), UNIQUE INDEX UNIQ_9238AB18B6D7A974 (game_plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_plant (id INT AUTO_INCREMENT NOT NULL, game_fruit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, maturation_time INT NOT NULL, oxygen INT NOT NULL, UNIQUE INDEX UNIQ_993074FD1185EAD6 (game_fruit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_fruit ADD CONSTRAINT FK_9238AB1874B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id)');
        $this->addSql('ALTER TABLE game_fruit ADD CONSTRAINT FK_9238AB18B6D7A974 FOREIGN KEY (game_plant_id) REFERENCES game_plant (id)');
        $this->addSql('ALTER TABLE game_plant ADD CONSTRAINT FK_993074FD1185EAD6 FOREIGN KEY (game_fruit_id) REFERENCES game_fruit (id)');
        $this->addSql('ALTER TABLE room_log ADD date DATETIME NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_plant DROP FOREIGN KEY FK_993074FD1185EAD6');
        $this->addSql('ALTER TABLE game_fruit DROP FOREIGN KEY FK_9238AB18B6D7A974');
        $this->addSql('DROP TABLE game_fruit');
        $this->addSql('DROP TABLE game_plant');
        $this->addSql('ALTER TABLE room_log DROP date, DROP created_at, DROP updated_at');
    }
}
