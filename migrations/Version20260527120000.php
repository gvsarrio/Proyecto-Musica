<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade nombre a banda y estado/es_administrador a miembro_banda';
    }

    public function up(Schema $schema): void
    {
        // Añadir nombre a la tabla banda (con default vacío para filas existentes)
        $this->addSql("ALTER TABLE banda ADD nombre VARCHAR(100) NOT NULL DEFAULT ''");
        // Asignar nombres de placeholder a bandas existentes
        $this->addSql("UPDATE banda SET nombre = CONCAT('Banda ', id) WHERE nombre = ''");

        // Añadir estado a miembro_banda (default 'aceptado' para miembros existentes)
        $this->addSql("ALTER TABLE miembro_banda ADD estado VARCHAR(20) NOT NULL DEFAULT 'aceptado'");
        // Hacer rol_banda nullable (antes era NOT NULL)
        $this->addSql("ALTER TABLE miembro_banda MODIFY rol_banda VARCHAR(100) NULL");
        // Añadir es_administrador a miembro_banda (default 0 para miembros existentes)
        $this->addSql("ALTER TABLE miembro_banda ADD es_administrador TINYINT(1) NOT NULL DEFAULT 0");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE banda DROP COLUMN nombre");
        $this->addSql("ALTER TABLE miembro_banda DROP COLUMN estado");
        $this->addSql("ALTER TABLE miembro_banda DROP COLUMN es_administrador");
        $this->addSql("ALTER TABLE miembro_banda MODIFY rol_banda VARCHAR(100) NOT NULL");
    }
}
