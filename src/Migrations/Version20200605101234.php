<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605101234 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE aime ADD id_utilisateur_id INT NOT NULL, ADD id_commentaire_id INT NOT NULL, DROP utilisateur, DROP commentaire');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE8C6EE5C49 FOREIGN KEY (id_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE aime ADD CONSTRAINT FK_8533FE887FA6C96 FOREIGN KEY (id_commentaire_id) REFERENCES commentaires (id)');
        $this->addSql('CREATE INDEX IDX_8533FE8C6EE5C49 ON aime (id_utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_8533FE887FA6C96 ON aime (id_commentaire_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE8C6EE5C49');
        $this->addSql('ALTER TABLE aime DROP FOREIGN KEY FK_8533FE887FA6C96');
        $this->addSql('DROP INDEX IDX_8533FE8C6EE5C49 ON aime');
        $this->addSql('DROP INDEX IDX_8533FE887FA6C96 ON aime');
        $this->addSql('ALTER TABLE aime ADD utilisateur INT NOT NULL, ADD commentaire INT NOT NULL, DROP id_utilisateur_id, DROP id_commentaire_id');
    }
}
