<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605221048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversacion (id INT AUTO_INCREMENT NOT NULL, fecha_creacion DATETIME NOT NULL, fecha_ultimo_mensaje DATETIME DEFAULT NULL, usuario_uno_id INT NOT NULL, usuario_dos_id INT NOT NULL, INDEX IDX_474049CFCC78EC83 (usuario_uno_id), INDEX IDX_474049CF235DFC2A (usuario_dos_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mensaje (id INT AUTO_INCREMENT NOT NULL, contenido LONGTEXT NOT NULL, fecha_envio DATETIME NOT NULL, leido TINYINT NOT NULL, conversacion_id INT NOT NULL, remitente_id INT NOT NULL, INDEX IDX_9B631D01ABD5A1D6 (conversacion_id), INDEX IDX_9B631D011C3E945F (remitente_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE conversacion ADD CONSTRAINT FK_474049CFCC78EC83 FOREIGN KEY (usuario_uno_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE conversacion ADD CONSTRAINT FK_474049CF235DFC2A FOREIGN KEY (usuario_dos_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01ABD5A1D6 FOREIGN KEY (conversacion_id) REFERENCES conversacion (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D011C3E945F FOREIGN KEY (remitente_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversacion DROP FOREIGN KEY FK_474049CFCC78EC83');
        $this->addSql('ALTER TABLE conversacion DROP FOREIGN KEY FK_474049CF235DFC2A');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01ABD5A1D6');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D011C3E945F');
        $this->addSql('DROP TABLE conversacion');
        $this->addSql('DROP TABLE mensaje');
        $this->addSql('ALTER TABLE instrumento_personalizado RENAME INDEX idx_9f716a96db38439e TO IDX_inst_pers_usuario');
        $this->addSql('ALTER TABLE musico_instrumento_personalizado RENAME INDEX idx_840ad3c879398f67 TO IDX_mip_musico');
        $this->addSql('ALTER TABLE musico_instrumento_personalizado RENAME INDEX idx_840ad3c827cfc36a TO IDX_mip_instrumento');
        $this->addSql('ALTER TABLE musico_instrumento_sistema RENAME INDEX idx_5fd5519a79398f67 TO IDX_mis_musico');
        $this->addSql('ALTER TABLE musico_instrumento_sistema RENAME INDEX idx_5fd5519a6060a947 TO IDX_mis_instrumento');
    }
}
