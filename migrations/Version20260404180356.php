<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404180356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE musico (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, telefono INT DEFAULT NULL, biografia LONGTEXT NOT NULL, ubicacion VARCHAR(255) NOT NULL, anyos_experiencia INT NOT NULL, imagen_url VARCHAR(255) DEFAULT NULL, creado_en DATETIME DEFAULT NULL, actualizado_en DATETIME DEFAULT NULL, es_banda TINYINT NOT NULL, user_id_id INT NOT NULL, UNIQUE INDEX UNIQ_99CB69E89D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE musico ADD CONSTRAINT FK_99CB69E89D86650F FOREIGN KEY (user_id_id) REFERENCES usuario (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE musico DROP FOREIGN KEY FK_99CB69E89D86650F');
        $this->addSql('DROP TABLE musico');
    }
}
