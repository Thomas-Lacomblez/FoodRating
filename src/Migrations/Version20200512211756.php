<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200512211756 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discussion (id_discussion INT AUTO_INCREMENT NOT NULL, id_utilisateur INT DEFAULT NULL, sujet CHAR(32) DEFAULT NULL, message CHAR(255) DEFAULT NULL, titre CHAR(32) DEFAULT NULL, creation DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C0B9F90F50EAE44 (id_utilisateur), INDEX I_FK_DISCUSSION_UTILISATEURS (id_utilisateur), PRIMARY KEY(id_discussion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponses (id_reponse INT AUTO_INCREMENT NOT NULL, id_utilisateur INT NOT NULL, id_discussion INT NOT NULL, message CHAR(32) DEFAULT NULL, INDEX I_FK_REPONSES_DISCUSSION (id_discussion), INDEX I_FK_REPONSES_UTILISATEURS (id_utilisateur), PRIMARY KEY(id_reponse)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90F50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE categories CHANGE id id VARCHAR(255) NOT NULL, CHANGE known known TINYINT(1) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE url url VARCHAR(255) NOT NULL, CHANGE same_as same_as VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notes CHANGE produit_id produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit CHANGE categorie categorie LONGTEXT NOT NULL, CHANGE etiquette etiquette LONGTEXT NOT NULL, CHANGE additif additif LONGTEXT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE discussion');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('ALTER TABLE categories CHANGE id id VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE known known INT NOT NULL, CHANGE name name VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE url url VARCHAR(200) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE same_as same_as VARCHAR(200) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE notes CHANGE produit_id produit_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE categorie categorie VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE etiquette etiquette VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE additif additif VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
