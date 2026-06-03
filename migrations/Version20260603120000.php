<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Datos iniciales: instrumentos del sistema y géneros musicales';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO instrumento_sistema (nombre) VALUES ('Voz'), ('Guitarra Eléctrica'), ('Bajo Eléctrico'), ('Batería'), ('Teclado / Piano'), ('Guitarra Acústica'), ('Saxofón'), ('Violín'), ('Sintetizador'), ('Percusión')");
        $this->addSql("INSERT IGNORE INTO genero (nombre) VALUES ('Rock'), ('Pop'), ('Jazz'), ('Blues'), ('Flamenco'), ('Clásica'), ('Metal'), ('Punk'), ('Reggae'), ('Hip-Hop'), ('Electrónica'), ('Folk'), ('R&B'), ('Soul'), ('Funk'), ('Country'), ('Latina'), ('Indie'), ('Alternativo')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM instrumento_sistema WHERE nombre IN ('Voz', 'Guitarra Eléctrica', 'Bajo Eléctrico', 'Batería', 'Teclado / Piano', 'Guitarra Acústica', 'Saxofón', 'Violín', 'Sintetizador', 'Percusión')");
        $this->addSql("DELETE FROM genero WHERE nombre IN ('Rock', 'Pop', 'Jazz', 'Blues', 'Flamenco', 'Clásica', 'Metal', 'Punk', 'Reggae', 'Hip-Hop', 'Electrónica', 'Folk', 'R&B', 'Soul', 'Funk', 'Country', 'Latina', 'Indie', 'Alternativo')");
    }
}
