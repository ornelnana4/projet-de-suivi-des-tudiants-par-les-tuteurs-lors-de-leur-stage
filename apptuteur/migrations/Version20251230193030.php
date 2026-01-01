<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230193030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ajouter uniquement les colonnes qui n'existent pas
        $this->addSql('ALTER TABLE visite ADD etudiant_id INT NOT NULL');
        $this->addSql('ALTER TABLE visite ADD statut VARCHAR(255) NOT NULL');

        // Ajouter la contrainte et l'index
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('CREATE INDEX IDX_B09C8CBBDDEAB1A3 ON visite (etudiant_id)');
    }


    public function down(Schema $schema): void
    {
        // Supprimer la contrainte foreign key si elle existe
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY IF EXISTS FK_B09C8CBBDDEAB1A3');

        // Supprimer l'index si il existe
        $this->addSql('DROP INDEX IF EXISTS IDX_B09C8CBBDDEAB1A3 ON visite');

        // Supprimer les colonnes uniquement si elles existent
        $this->addSql('ALTER TABLE visite DROP COLUMN IF EXISTS etudiant_id');
        $this->addSql('ALTER TABLE visite DROP COLUMN IF EXISTS compte_rendu');
        $this->addSql('ALTER TABLE visite DROP COLUMN IF EXISTS statut');
    }
}
