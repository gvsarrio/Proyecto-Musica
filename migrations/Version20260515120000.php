<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260515120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade usuario_id a instrumento para instrumentos privados por usuario';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE instrumento ADD usuario_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE instrumento ADD CONSTRAINT FK_instrumento_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_instrumento_usuario ON instrumento (usuario_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE instrumento DROP FOREIGN KEY FK_instrumento_usuario');
        $this->addSql('DROP INDEX IDX_instrumento_usuario ON instrumento');
        $this->addSql('ALTER TABLE instrumento DROP COLUMN usuario_id');
    }
}
