<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230812155734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_modifier_config ADD modifier_strategy VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD priority INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP replace_event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE abstract_modifier_config ADD replace_event BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP modifier_strategy');
        $this->addSql('ALTER TABLE abstract_modifier_config DROP priority');
    }
}
