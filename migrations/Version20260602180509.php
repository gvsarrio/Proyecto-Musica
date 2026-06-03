<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602180509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS genero (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS banda_genero (banda_id INT NOT NULL, genero_id INT NOT NULL, INDEX IDX_385989899EFB0C1D (banda_id), INDEX IDX_38598989BCE7B795 (genero_id), PRIMARY KEY (banda_id, genero_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS musico_genero (musico_id INT NOT NULL, genero_id INT NOT NULL, INDEX IDX_180AAEBF79398F67 (musico_id), INDEX IDX_180AAEBFBCE7B795 (genero_id), PRIMARY KEY (musico_id, genero_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE banda_genero ADD CONSTRAINT IF NOT EXISTS FK_385989899EFB0C1D FOREIGN KEY (banda_id) REFERENCES banda (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE banda_genero ADD CONSTRAINT IF NOT EXISTS FK_38598989BCE7B795 FOREIGN KEY (genero_id) REFERENCES genero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE musico_genero ADD CONSTRAINT IF NOT EXISTS FK_180AAEBF79398F67 FOREIGN KEY (musico_id) REFERENCES musico (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE musico_genero ADD CONSTRAINT IF NOT EXISTS FK_180AAEBFBCE7B795 FOREIGN KEY (genero_id) REFERENCES genero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE banda CHANGE nombre nombre VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE miembro_banda CHANGE estado estado VARCHAR(20) NOT NULL, CHANGE es_administrador es_administrador TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banda_genero DROP FOREIGN KEY FK_385989899EFB0C1D');
        $this->addSql('ALTER TABLE banda_genero DROP FOREIGN KEY FK_38598989BCE7B795');
        $this->addSql('ALTER TABLE musico_genero DROP FOREIGN KEY FK_180AAEBF79398F67');
        $this->addSql('ALTER TABLE musico_genero DROP FOREIGN KEY FK_180AAEBFBCE7B795');
        $this->addSql('DROP TABLE banda_genero');
        $this->addSql('DROP TABLE genero');
        $this->addSql('DROP TABLE musico_genero');
        $this->addSql('ALTER TABLE banda CHANGE nombre nombre VARCHAR(100) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE instrumento_personalizado RENAME INDEX idx_9f716a96db38439e TO FK_inst_pers_usuario');
        $this->addSql('ALTER TABLE miembro_banda CHANGE estado estado VARCHAR(20) DEFAULT \'aceptado\' NOT NULL, CHANGE es_administrador es_administrador TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE musico_instrumento_personalizado RENAME INDEX idx_840ad3c827cfc36a TO FK_mip_instrumento');
        $this->addSql('ALTER TABLE musico_instrumento_sistema RENAME INDEX idx_5fd5519a6060a947 TO FK_mis_instrumento');
    }
}
