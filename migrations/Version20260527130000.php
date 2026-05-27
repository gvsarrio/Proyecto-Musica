<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade latitud y longitud a la tabla banda';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE banda ADD latitud DOUBLE PRECISION DEFAULT NULL, ADD longitud DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE banda DROP COLUMN latitud, DROP COLUMN longitud');
    }
}
