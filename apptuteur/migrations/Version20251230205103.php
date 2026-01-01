<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230205103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E386EC68D8');
        $this->addSql('DROP INDEX IDX_717E22E386EC68D8 ON etudiant');
        $this->addSql('ALTER TABLE etudiant DROP tuteur_id');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('CREATE INDEX IDX_B09C8CBBDDEAB1A3 ON visite (etudiant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBDDEAB1A3');
        $this->addSql('DROP INDEX IDX_B09C8CBBDDEAB1A3 ON visite');
        $this->addSql('ALTER TABLE etudiant ADD tuteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E386EC68D8 FOREIGN KEY (tuteur_id) REFERENCES tuteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_717E22E386EC68D8 ON etudiant (tuteur_id)');
    }
}
