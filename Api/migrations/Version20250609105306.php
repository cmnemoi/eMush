<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609105306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_cooked INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_planets_fully_scanned INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_tech_successes INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_tech_fails INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_link_improved INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_caressed INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_hunters_destroyed INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_lost_cycles INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_killed INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_talked INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_actions_done INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_action_points_used INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_action_points_wasted INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_eaten INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_slept_cycles INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_died_during_sleep BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_times_hacked INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_link_fixed INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_sleep_interupted INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_mutant_damage_dealt INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_injuries_contracted INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_illnesses_contracted INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_drugs_taken INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_knife_dodged INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_attacked_times INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_kube_used INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_traitor_used INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_uncovered_secret_actions_taken INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE player_info ADD player_statistics_revealed_secret_actions_taken INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_cooked');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_planets_fully_scanned');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_tech_successes');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_tech_fails');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_link_improved');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_caressed');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_hunters_destroyed');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_lost_cycles');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_killed');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_talked');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_actions_done');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_action_points_used');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_action_points_wasted');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_eaten');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_slept_cycles');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_died_during_sleep');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_times_hacked');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_link_fixed');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_sleep_interupted');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_mutant_damage_dealt');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_injuries_contracted');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_illnesses_contracted');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_drugs_taken');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_knife_dodged');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_attacked_times');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_kube_used');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_traitor_used');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_uncovered_secret_actions_taken');
        $this->addSql('ALTER TABLE player_info DROP player_statistics_revealed_secret_actions_taken');
    }
}
