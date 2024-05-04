<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504094620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD last_participant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD last_participant_number_of_participations INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEF31B399D FOREIGN KEY (last_participant_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEF31B399D ON project (last_participant_id)');
        $this->addSql('ALTER TABLE status_target DROP CONSTRAINT fk_fb2587b1166d1f9c');
        $this->addSql('DROP INDEX idx_fb2587b1166d1f9c');
        $this->addSql('ALTER TABLE status_target DROP project_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_target ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status_target ADD CONSTRAINT fk_fb2587b1166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fb2587b1166d1f9c ON status_target (project_id)');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEF31B399D');
        $this->addSql('DROP INDEX IDX_2FB3D0EEF31B399D');
        $this->addSql('ALTER TABLE project DROP last_participant_id');
        $this->addSql('ALTER TABLE project DROP last_participant_number_of_participations');
    }
}
