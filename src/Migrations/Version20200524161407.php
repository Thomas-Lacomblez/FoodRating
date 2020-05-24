<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200524161407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, idUtilisateur INT NOT NULL, idDiscussion INT NOT NULL, INDEX IDX_5FB6DEC75D419CCB (idUtilisateur), INDEX IDX_5FB6DEC7424DE7E5 (idDiscussion), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC75D419CCB FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7424DE7E5 FOREIGN KEY (idDiscussion) REFERENCES discussion (id_discussion)');
        $this->addSql('DROP TABLE reponses');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reponses (id_reponse INT AUTO_INCREMENT NOT NULL, id_utilisateur INT NOT NULL, id_discussion INT NOT NULL, message CHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL, INDEX I_FK_REPONSES_UTILISATEURS (id_utilisateur), INDEX I_FK_REPONSES_DISCUSSION (id_discussion), PRIMARY KEY(id_reponse)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE reponse');
    }
}
