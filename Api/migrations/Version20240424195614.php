<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424195614 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus ADD number_of_projects_by_batch INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE config_daedalus ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_oxygen SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_fuel SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_hull SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_shield SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_oxygen SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_fuel SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_hull SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_shield SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER daily_spore_nb SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER nb_mush SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER cycle_per_game_day SET DEFAULT 0');
        $this->addSql('ALTER TABLE config_daedalus ALTER cycle_length SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config_daedalus DROP number_of_projects_by_batch');
        $this->addSql('ALTER TABLE config_daedalus ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_oxygen DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_fuel DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_hull DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER init_shield DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_oxygen DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_fuel DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_hull DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER max_shield DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER daily_spore_nb DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER nb_mush DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER cycle_per_game_day DROP DEFAULT');
        $this->addSql('ALTER TABLE config_daedalus ALTER cycle_length DROP DEFAULT');
    }
}
