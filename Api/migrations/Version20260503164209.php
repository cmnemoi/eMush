<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503164209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_modifier_config ADD event_to_remove VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD event_to_add VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD weight INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD action VARCHAR(255) DEFAULT \'replace\'');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD criteria VARCHAR(255) DEFAULT \'event_name\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_modifier_config DROP event_to_remove');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP event_to_add');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP weight');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP action');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP criteria');
    }
}
