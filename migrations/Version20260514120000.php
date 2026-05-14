<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade columnas latitud y longitud a la tabla musico';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE musico ADD latitud DOUBLE PRECISION DEFAULT NULL, ADD longitud DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE musico DROP latitud, DROP longitud');
    }
}
