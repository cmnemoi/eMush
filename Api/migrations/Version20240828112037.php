<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240828112037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sanction_evidence ADD commander_mission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sanction_evidence ADD CONSTRAINT FK_31059F501248019F FOREIGN KEY (commander_mission_id) REFERENCES commander_mission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_31059F501248019F ON sanction_evidence (commander_mission_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sanction_evidence DROP CONSTRAINT FK_31059F501248019F');
        $this->addSql('DROP INDEX IDX_31059F501248019F');
        $this->addSql('ALTER TABLE sanction_evidence DROP commander_mission_id');
    }
}
