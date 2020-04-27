<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200427091049 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, produit_id INT DEFAULT NULL, message LONGTEXT NOT NULL, INDEX IDX_D9BEC0C4FB88E14F (utilisateur_id), INDEX IDX_D9BEC0C4F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, produit_id INT NOT NULL, nb_etoiles INT NOT NULL, INDEX IDX_11BA68CFB88E14F (utilisateur_id), INDEX IDX_11BA68CF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, ingredient LONGTEXT NOT NULL, trace LONGTEXT NOT NULL, marque VARCHAR(255) NOT NULL, categorie LONGTEXT NOT NULL, distributeur VARCHAR(255) NOT NULL, etiquette LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, kcal VARCHAR(20) NOT NULL, additif LONGTEXT NOT NULL, nutriscore VARCHAR(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4F347EFB');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF347EFB');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4FB88E14F');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CFB88E14F');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE utilisateurs');
    }
}
