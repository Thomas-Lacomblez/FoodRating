<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200619162700 extends AbstractMigration
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
        $this->addSql('CREATE TABLE amis (id INT AUTO_INCREMENT NOT NULL, utilisateur1_id INT DEFAULT NULL, utilisateur2_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_9FE2E76130F4F973 (utilisateur1_id), INDEX IDX_9FE2E7612241569D (utilisateur2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id VARCHAR(255) NOT NULL, known TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, products INT NOT NULL, url VARCHAR(255) NOT NULL, same_as VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, message LONGTEXT NOT NULL, produit_id VARCHAR(255) NOT NULL, utile INT DEFAULT NULL, INDEX IDX_D9BEC0C4FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande_ami (id INT AUTO_INCREMENT NOT NULL, demandeur_id INT DEFAULT NULL, recepteur_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_2A51AD8895A6EE59 (demandeur_id), INDEX IDX_2A51AD883B49782D (recepteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussion (id_discussion INT AUTO_INCREMENT NOT NULL, id_utilisateur INT DEFAULT NULL, sujet CHAR(32) DEFAULT NULL, message CHAR(255) DEFAULT NULL, titre CHAR(32) DEFAULT NULL, creation DATETIME DEFAULT NULL, INDEX I_FK_DISCUSSION_UTILISATEURS (id_utilisateur), PRIMARY KEY(id_discussion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussion_privee (id INT AUTO_INCREMENT NOT NULL, amis_id INT DEFAULT NULL, envoyeur_disc_id INT DEFAULT NULL, recepteur_disc_id INT DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5C8EE24B706F82C7 (amis_id), INDEX IDX_5C8EE24BBF709C84 (envoyeur_disc_id), INDEX IDX_5C8EE24BC1A96162 (recepteur_disc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moyenne_produits (id INT AUTO_INCREMENT NOT NULL, moyenne DOUBLE PRECISION NOT NULL, produit_id VARCHAR(255) NOT NULL, categorie_produit VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, nb_etoiles INT NOT NULL, produit_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_11BA68CFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, idUtilisateur INT DEFAULT NULL, idDiscussion INT NOT NULL, INDEX IDX_5FB6DEC75D419CCB (idUtilisateur), INDEX IDX_5FB6DEC7424DE7E5 (idDiscussion), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse_privee (id INT AUTO_INCREMENT NOT NULL, amis_id INT DEFAULT NULL, discussion_id INT DEFAULT NULL, envoyeur_rep_id INT DEFAULT NULL, recepteur_rep_id INT DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_DEA6600A706F82C7 (amis_id), INDEX IDX_DEA6600A1ADED311 (discussion_id), INDEX IDX_DEA6600A7781B024 (envoyeur_rep_id), INDEX IDX_DEA6600A1F00EB29 (recepteur_rep_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, vkey VARCHAR(255) NOT NULL, verified TINYINT(1) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', image_base64 LONGTEXT DEFAULT NULL, nombre_signalement INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE8C6EE5C49 FOREIGN KEY (id_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE887FA6C96 FOREIGN KEY (id_commentaire_id) REFERENCES commentaires (id)');
        $this->addSql('ALTER TABLE amis ADD CONSTRAINT FK_9FE2E76130F4F973 FOREIGN KEY (utilisateur1_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE amis ADD CONSTRAINT FK_9FE2E7612241569D FOREIGN KEY (utilisateur2_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE demande_ami ADD CONSTRAINT FK_2A51AD8895A6EE59 FOREIGN KEY (demandeur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE demande_ami ADD CONSTRAINT FK_2A51AD883B49782D FOREIGN KEY (recepteur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90F50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24B706F82C7 FOREIGN KEY (amis_id) REFERENCES amis (id)');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24BBF709C84 FOREIGN KEY (envoyeur_disc_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24BC1A96162 FOREIGN KEY (recepteur_disc_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC75D419CCB FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7424DE7E5 FOREIGN KEY (idDiscussion) REFERENCES discussion (id_discussion)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A706F82C7 FOREIGN KEY (amis_id) REFERENCES amis (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion_privee (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A7781B024 FOREIGN KEY (envoyeur_rep_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A1F00EB29 FOREIGN KEY (recepteur_rep_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateurs (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discussion_privee DROP FOREIGN KEY FK_5C8EE24B706F82C7');
        $this->addSql('ALTER TABLE reponse_privee DROP FOREIGN KEY FK_DEA6600A706F82C7');
        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE887FA6C96');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7424DE7E5');
        $this->addSql('ALTER TABLE reponse_privee DROP FOREIGN KEY FK_DEA6600A1ADED311');
        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE8C6EE5C49');
        $this->addSql('ALTER TABLE amis DROP FOREIGN KEY FK_9FE2E76130F4F973');
        $this->addSql('ALTER TABLE amis DROP FOREIGN KEY FK_9FE2E7612241569D');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4FB88E14F');
        $this->addSql('ALTER TABLE demande_ami DROP FOREIGN KEY FK_2A51AD8895A6EE59');
        $this->addSql('ALTER TABLE demande_ami DROP FOREIGN KEY FK_2A51AD883B49782D');
        $this->addSql('ALTER TABLE discussion DROP FOREIGN KEY FK_C0B9F90F50EAE44');
        $this->addSql('ALTER TABLE discussion_privee DROP FOREIGN KEY FK_5C8EE24BBF709C84');
        $this->addSql('ALTER TABLE discussion_privee DROP FOREIGN KEY FK_5C8EE24BC1A96162');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CFB88E14F');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC75D419CCB');
        $this->addSql('ALTER TABLE reponse_privee DROP FOREIGN KEY FK_DEA6600A7781B024');
        $this->addSql('ALTER TABLE reponse_privee DROP FOREIGN KEY FK_DEA6600A1F00EB29');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE aime');
        $this->addSql('DROP TABLE amis');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE demande_ami');
        $this->addSql('DROP TABLE discussion');
        $this->addSql('DROP TABLE discussion_privee');
        $this->addSql('DROP TABLE moyenne_produits');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE reponse_privee');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE utilisateurs');
    }
}
