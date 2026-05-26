<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separa instrumento en instrumento_sistema e instrumento_personalizado, con tablas de relación ManyToMany propias';
    }

    public function up(Schema $schema): void
    {
        // 1. Crear instrumento_sistema conservando los IDs de los instrumentos del sistema
        $this->addSql('CREATE TABLE instrumento_sistema (id INT NOT NULL AUTO_INCREMENT, nombre VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO instrumento_sistema (id, nombre) SELECT id, nombre FROM instrumento WHERE usuario_id IS NULL');

        // 2. Crear instrumento_personalizado conservando los IDs de los instrumentos de usuario
        $this->addSql('CREATE TABLE instrumento_personalizado (id INT NOT NULL AUTO_INCREMENT, nombre VARCHAR(50) NOT NULL, usuario_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO instrumento_personalizado (id, nombre, usuario_id) SELECT id, nombre, usuario_id FROM instrumento WHERE usuario_id IS NOT NULL');
        $this->addSql('ALTER TABLE instrumento_personalizado ADD CONSTRAINT FK_inst_pers_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_inst_pers_usuario ON instrumento_personalizado (usuario_id)');

        // 3. Crear tabla de relación musico <-> instrumento_sistema (migrar datos existentes)
        $this->addSql('CREATE TABLE musico_instrumento_sistema (musico_id INT NOT NULL, instrumento_sistema_id INT NOT NULL, PRIMARY KEY(musico_id, instrumento_sistema_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO musico_instrumento_sistema (musico_id, instrumento_sistema_id) SELECT im.musico_id, i.id FROM instrumento_musico im JOIN instrumento i ON im.instrumento_id = i.id WHERE i.usuario_id IS NULL');
        $this->addSql('ALTER TABLE musico_instrumento_sistema ADD CONSTRAINT FK_mis_musico FOREIGN KEY (musico_id) REFERENCES musico (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE musico_instrumento_sistema ADD CONSTRAINT FK_mis_instrumento FOREIGN KEY (instrumento_sistema_id) REFERENCES instrumento_sistema (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_mis_musico ON musico_instrumento_sistema (musico_id)');
        $this->addSql('CREATE INDEX IDX_mis_instrumento ON musico_instrumento_sistema (instrumento_sistema_id)');

        // 4. Crear tabla de relación musico <-> instrumento_personalizado (migrar datos existentes)
        $this->addSql('CREATE TABLE musico_instrumento_personalizado (musico_id INT NOT NULL, instrumento_personalizado_id INT NOT NULL, PRIMARY KEY(musico_id, instrumento_personalizado_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO musico_instrumento_personalizado (musico_id, instrumento_personalizado_id) SELECT im.musico_id, i.id FROM instrumento_musico im JOIN instrumento i ON im.instrumento_id = i.id WHERE i.usuario_id IS NOT NULL');
        $this->addSql('ALTER TABLE musico_instrumento_personalizado ADD CONSTRAINT FK_mip_musico FOREIGN KEY (musico_id) REFERENCES musico (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE musico_instrumento_personalizado ADD CONSTRAINT FK_mip_instrumento FOREIGN KEY (instrumento_personalizado_id) REFERENCES instrumento_personalizado (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_mip_musico ON musico_instrumento_personalizado (musico_id)');
        $this->addSql('CREATE INDEX IDX_mip_instrumento ON musico_instrumento_personalizado (instrumento_personalizado_id)');

        // 5. Eliminar tablas antiguas
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B779398F67');
        $this->addSql('ALTER TABLE instrumento_musico DROP FOREIGN KEY FK_9D06A2B740B7B70');
        $this->addSql('DROP TABLE instrumento_musico');
        $this->addSql('ALTER TABLE instrumento DROP FOREIGN KEY FK_instrumento_usuario');
        $this->addSql('DROP TABLE instrumento');
    }

    public function down(Schema $schema): void
    {
        // Recrear tabla instrumento unificada
        $this->addSql('CREATE TABLE instrumento (id INT NOT NULL AUTO_INCREMENT, nombre VARCHAR(50) NOT NULL, usuario_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO instrumento (id, nombre) SELECT id, nombre FROM instrumento_sistema');
        $this->addSql('INSERT INTO instrumento (id, nombre, usuario_id) SELECT id, nombre, usuario_id FROM instrumento_personalizado');
        $this->addSql('ALTER TABLE instrumento ADD CONSTRAINT FK_instrumento_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_instrumento_usuario ON instrumento (usuario_id)');

        // Recrear tabla instrumento_musico
        $this->addSql('CREATE TABLE instrumento_musico (id INT NOT NULL AUTO_INCREMENT, musico_id INT NOT NULL, instrumento_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('INSERT INTO instrumento_musico (musico_id, instrumento_id) SELECT musico_id, instrumento_sistema_id FROM musico_instrumento_sistema');
        $this->addSql('INSERT INTO instrumento_musico (musico_id, instrumento_id) SELECT musico_id, instrumento_personalizado_id FROM musico_instrumento_personalizado');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_instrumento_musico_musico FOREIGN KEY (musico_id) REFERENCES musico (id)');
        $this->addSql('ALTER TABLE instrumento_musico ADD CONSTRAINT FK_instrumento_musico_instrumento FOREIGN KEY (instrumento_id) REFERENCES instrumento (id)');

        // Eliminar tablas nuevas
        $this->addSql('DROP TABLE musico_instrumento_personalizado');
        $this->addSql('DROP TABLE musico_instrumento_sistema');
        $this->addSql('DROP TABLE instrumento_personalizado');
        $this->addSql('DROP TABLE instrumento_sistema');
    }
}
