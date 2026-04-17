<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417165602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE banda (id INT AUTO_INCREMENT NOT NULL, biografia LONGTEXT NOT NULL, generos VARCHAR(200) DEFAULT NULL, anyo_formacion INT NOT NULL, ubicacion VARCHAR(255) NOT NULL, imagen_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instrumento (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instrumento_musico (id INT AUTO_INCREMENT NOT NULL, musico_id INT NOT NULL, instrumento_id INT NOT NULL, INDEX IDX_9D06A2B779398F67 (musico_id), INDEX IDX_9D06A2B740B7B70 (instrumento_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE miembro_banda (id INT AUTO_INCREMENT NOT NULL, rol_banda VARCHAR(100) NOT NULL, banda_id INT NOT NULL, musico_id INT NOT NULL, INDEX IDX_F3090B6C9EFB0C1D (banda_id), INDEX IDX_F3090B6C79398F67 (musico_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE musico (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, telefono INT DEFAULT NULL, biografia LONGTEXT NOT NULL, ubicacion VARCHAR(255) NOT NULL, anyos_experiencia INT NOT NULL, imagen_url VARCHAR(255) DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, es_banda TINYINT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_99CB69E8A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_9D06A2B779398F67 FOREIGN KEY (musico_id) REFERENCES musico (id)');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_9D06A2B740B7B70 FOREIGN KEY (instrumento_id) REFERENCES instrumento (id)');
        $this->addSql('ALTER TABLE miembro_banda ADD CONSTRAINT FK_F3090B6C9EFB0C1D FOREIGN KEY (banda_id) REFERENCES banda (id)');
        $this->addSql('ALTER TABLE miembro_banda ADD CONSTRAINT FK_F3090B6C79398F67 FOREIGN KEY (musico_id) REFERENCES musico (id)');
        $this->addSql('ALTER TABLE musico ADD CONSTRAINT FK_99CB69E8A76ED395 FOREIGN KEY (user_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B779398F67');
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B740B7B70');
        $this->addSql('ALTER TABLE miembro_banda DROP FOREIGN KEY FK_F3090B6C9EFB0C1D');
        $this->addSql('ALTER TABLE miembro_banda DROP FOREIGN KEY FK_F3090B6C79398F67');
        $this->addSql('ALTER TABLE musico DROP FOREIGN KEY FK_99CB69E8A76ED395');
        $this->addSql('DROP TABLE banda');
        $this->addSql('DROP TABLE instrumento');
        $this->addSql('DROP TABLE instrumento_musico');
        $this->addSql('DROP TABLE miembro_banda');
        $this->addSql('DROP TABLE musico');
    }
}
