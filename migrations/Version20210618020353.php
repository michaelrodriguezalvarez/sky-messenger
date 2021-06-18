<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210618020353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actividad (id INT AUTO_INCREMENT NOT NULL, usuario INT DEFAULT NULL, last_activity_at DATETIME DEFAULT NULL, estado VARCHAR(255) NOT NULL, codigo VARCHAR(255) DEFAULT NULL, INDEX fk_usuario_actividad (usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mensaje (id INT AUTO_INCREMENT NOT NULL, destinatario INT DEFAULT NULL, remitente INT DEFAULT NULL, texto TEXT NOT NULL, fecha DATETIME NOT NULL, INDEX fk_mensaje_remitente (remitente), INDEX fk_mensaje_destinatario (destinatario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE perfil (id INT AUTO_INCREMENT NOT NULL, usuario INT DEFAULT NULL, nick VARCHAR(255) NOT NULL, nombre VARCHAR(255) DEFAULT NULL, apellidos VARCHAR(255) DEFAULT NULL, direccion TEXT DEFAULT NULL, telefono VARCHAR(255) DEFAULT NULL, sexo VARCHAR(255) NOT NULL, descripcion TEXT DEFAULT NULL, INDEX fk_perfil_usuario (usuario), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD062265B05D FOREIGN KEY (usuario) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01A7399187 FOREIGN KEY (destinatario) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D0151A5ACA4 FOREIGN KEY (remitente) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE perfil ADD CONSTRAINT FK_966576472265B05D FOREIGN KEY (usuario) REFERENCES `user` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD062265B05D');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01A7399187');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D0151A5ACA4');
        $this->addSql('ALTER TABLE perfil DROP FOREIGN KEY FK_966576472265B05D');
        $this->addSql('DROP TABLE actividad');
        $this->addSql('DROP TABLE mensaje');
        $this->addSql('DROP TABLE perfil');
        $this->addSql('DROP TABLE `user`');
    }
}
