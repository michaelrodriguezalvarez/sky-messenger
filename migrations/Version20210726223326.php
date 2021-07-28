<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210726223326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE configuracion');
        $this->addSql('ALTER TABLE perfil ADD avatar VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE configuracion (id INT AUTO_INCREMENT NOT NULL, usuario INT DEFAULT NULL, avisar TINYINT(1) NOT NULL, INDEX fk_configuracion_usuario (usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE configuracion ADD CONSTRAINT FK_682CCAF12265B05D FOREIGN KEY (usuario) REFERENCES user (id)');
        $this->addSql('ALTER TABLE perfil DROP avatar');
    }
}
