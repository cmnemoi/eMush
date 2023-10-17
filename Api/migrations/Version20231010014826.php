<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010014826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_planet_space_coordinates');
        $this->addSql('ALTER TABLE planet ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD daedalus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA574B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_68136AA599E6F5DF ON planet (player_id)');
        $this->addSql('CREATE INDEX IDX_68136AA574B5A52D ON planet (daedalus_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_planet_for_daedalus ON planet (name, orientation, distance, daedalus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planet DROP CONSTRAINT FK_68136AA599E6F5DF');
        $this->addSql('ALTER TABLE planet DROP CONSTRAINT FK_68136AA574B5A52D');
        $this->addSql('DROP INDEX IDX_68136AA599E6F5DF');
        $this->addSql('DROP INDEX IDX_68136AA574B5A52D');
        $this->addSql('DROP INDEX unique_planet_for_daedalus');
        $this->addSql('ALTER TABLE planet DROP player_id');
        $this->addSql('ALTER TABLE planet DROP daedalus_id');
        $this->addSql('CREATE UNIQUE INDEX unique_planet_space_coordinates ON planet (orientation, distance)');
    }
}
