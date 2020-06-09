<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609132835 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE aime (id INT AUTO_INCREMENT NOT NULL, id_utilisateur_id INT NOT NULL, id_commentaire_id INT NOT NULL, produit VARCHAR(255) NOT NULL, INDEX IDX_8533FE8C6EE5C49 (id_utilisateur_id), INDEX IDX_8533FE887FA6C96 (id_commentaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id VARCHAR(255) NOT NULL, known TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, products INT NOT NULL, url VARCHAR(255) NOT NULL, same_as VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, message LONGTEXT NOT NULL, produit_id VARCHAR(255) NOT NULL, utile INT DEFAULT NULL, INDEX IDX_D9BEC0C4FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussion (id_discussion INT AUTO_INCREMENT NOT NULL, id_utilisateur INT DEFAULT NULL, sujet CHAR(32) DEFAULT NULL, message CHAR(255) DEFAULT NULL, titre CHAR(32) DEFAULT NULL, creation DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C0B9F90F50EAE44 (id_utilisateur), INDEX I_FK_DISCUSSION_UTILISATEURS (id_utilisateur), PRIMARY KEY(id_discussion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moyenne_produits (id INT AUTO_INCREMENT NOT NULL, moyenne DOUBLE PRECISION NOT NULL, produit_id VARCHAR(255) NOT NULL, categorie_produit VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, nb_etoiles INT NOT NULL, produit_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_11BA68CFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, idUtilisateur INT NOT NULL, idDiscussion INT NOT NULL, INDEX IDX_5FB6DEC75D419CCB (idUtilisateur), INDEX IDX_5FB6DEC7424DE7E5 (idDiscussion), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', image_base64 LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE8C6EE5C49 FOREIGN KEY (id_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE887FA6C96 FOREIGN KEY (id_commentaire_id) REFERENCES commentaires (id)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90F50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC75D419CCB FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7424DE7E5 FOREIGN KEY (idDiscussion) REFERENCES discussion (id_discussion)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE887FA6C96');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7424DE7E5');
        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE8C6EE5C49');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4FB88E14F');
        $this->addSql('ALTER TABLE discussion DROP FOREIGN KEY FK_C0B9F90F50EAE44');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CFB88E14F');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC75D419CCB');
        $this->addSql('DROP TABLE aime');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE discussion');
        $this->addSql('DROP TABLE moyenne_produits');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE utilisateurs');
    }
}
