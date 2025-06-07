<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607183131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_projects_statistics_neron_projets_completed TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_projects_statistics_research_projets_completed TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('ALTER TABLE daedalus_info ADD daedalus_projects_statistics_pilgred_projets_completed TEXT DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN daedalus_info.daedalus_projects_statistics_neron_projets_completed IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN daedalus_info.daedalus_projects_statistics_research_projets_completed IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN daedalus_info.daedalus_projects_statistics_pilgred_projets_completed IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_projects_statistics_neron_projets_completed');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_projects_statistics_research_projets_completed');
        $this->addSql('ALTER TABLE daedalus_info DROP daedalus_projects_statistics_pilgred_projets_completed');
    }
}
