<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125172245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pending_statistic DROP CONSTRAINT fk_2f18511740ac2f6a');
        $this->addSql('DROP INDEX idx_2f18511740ac2f6a');
        $this->addSql('DROP INDEX unique_pending_statistic_per_user_and_daedalus');
        $this->addSql('ALTER TABLE pending_statistic RENAME COLUMN daedalus_info_id TO closed_daedalus_id');
        $this->addSql('ALTER TABLE pending_statistic ADD CONSTRAINT FK_2F185117BBC83F78 FOREIGN KEY (closed_daedalus_id) REFERENCES daedalus_closed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2F185117BBC83F78 ON pending_statistic (closed_daedalus_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_pending_statistic_per_user_and_closed_daedalus ON pending_statistic (config_id, user_id, closed_daedalus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pending_statistic DROP CONSTRAINT FK_2F185117BBC83F78');
        $this->addSql('DROP INDEX IDX_2F185117BBC83F78');
        $this->addSql('DROP INDEX unique_pending_statistic_per_user_and_closed_daedalus');
        $this->addSql('ALTER TABLE pending_statistic RENAME COLUMN closed_daedalus_id TO daedalus_info_id');
        $this->addSql('ALTER TABLE pending_statistic ADD CONSTRAINT fk_2f18511740ac2f6a FOREIGN KEY (daedalus_info_id) REFERENCES daedalus_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2f18511740ac2f6a ON pending_statistic (daedalus_info_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_pending_statistic_per_user_and_daedalus ON pending_statistic (config_id, user_id, daedalus_info_id)');
    }
}
