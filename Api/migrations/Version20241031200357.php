<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031200357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hunter_target ADD hunter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hunter_target ADD CONSTRAINT FK_C0DC648EA7DC5C81 FOREIGN KEY (hunter_id) REFERENCES hunter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C0DC648EA7DC5C81 ON hunter_target (hunter_id)');
        $this->addSql('ALTER TABLE project_requirement ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE project_requirement ALTER type SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE project_requirement ALTER target SET DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_requirement ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE project_requirement ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE project_requirement ALTER target DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_target DROP CONSTRAINT FK_C0DC648EA7DC5C81');
        $this->addSql('DROP INDEX IDX_C0DC648EA7DC5C81');
        $this->addSql('ALTER TABLE hunter_target DROP hunter_id');
    }
}
