<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405180715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE instrumento_musico (id INT AUTO_INCREMENT NOT NULL, musico_id INT NOT NULL, instrumento_id INT NOT NULL, INDEX IDX_9D06A2B779398F67 (musico_id), INDEX IDX_9D06A2B740B7B70 (instrumento_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_9D06A2B779398F67 FOREIGN KEY (musico_id) REFERENCES musico (id)');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_9D06A2B740B7B70 FOREIGN KEY (instrumento_id) REFERENCES instrumento (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B779398F67');
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B740B7B70');
        $this->addSql('DROP TABLE instrumento_musico');
    }
}
