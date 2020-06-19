<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618232911 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discussion_privee (id INT AUTO_INCREMENT NOT NULL, amis_id INT DEFAULT NULL, envoyeur_disc_id INT DEFAULT NULL, recepteur_disc_id INT DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5C8EE24B706F82C7 (amis_id), INDEX IDX_5C8EE24BBF709C84 (envoyeur_disc_id), INDEX IDX_5C8EE24BC1A96162 (recepteur_disc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse_privee (id INT AUTO_INCREMENT NOT NULL, amis_id INT DEFAULT NULL, discussion_id INT DEFAULT NULL, envoyeur_rep_id INT DEFAULT NULL, recepteur_rep_id INT DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_DEA6600A706F82C7 (amis_id), INDEX IDX_DEA6600A1ADED311 (discussion_id), INDEX IDX_DEA6600A7781B024 (envoyeur_rep_id), INDEX IDX_DEA6600A1F00EB29 (recepteur_rep_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24B706F82C7 FOREIGN KEY (amis_id) REFERENCES amis (id)');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24BBF709C84 FOREIGN KEY (envoyeur_disc_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE discussion_privee ADD CONSTRAINT FK_5C8EE24BC1A96162 FOREIGN KEY (recepteur_disc_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A706F82C7 FOREIGN KEY (amis_id) REFERENCES amis (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion_privee (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A7781B024 FOREIGN KEY (envoyeur_rep_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reponse_privee ADD CONSTRAINT FK_DEA6600A1F00EB29 FOREIGN KEY (recepteur_rep_id) REFERENCES utilisateurs (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reponse_privee DROP FOREIGN KEY FK_DEA6600A1ADED311');
        $this->addSql('DROP TABLE discussion_privee');
        $this->addSql('DROP TABLE reponse_privee');
    }
}
