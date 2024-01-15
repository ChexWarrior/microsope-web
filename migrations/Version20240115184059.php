<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115184059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__history AS SELECT id, description, excluded, included, focus FROM history');
        $this->addSql('DROP TABLE history');
        $this->addSql('CREATE TABLE history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, excluded CLOB DEFAULT NULL --(DC2Type:json)
        , included CLOB DEFAULT NULL --(DC2Type:json)
        , focus VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO history (id, description, excluded, included, focus) SELECT id, description, excluded, included, focus FROM __temp__history');
        $this->addSql('DROP TABLE __temp__history');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__history AS SELECT id, description, excluded, included, focus FROM history');
        $this->addSql('DROP TABLE history');
        $this->addSql('CREATE TABLE history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, excluded CLOB DEFAULT NULL --(DC2Type:array)
        , included CLOB DEFAULT NULL --(DC2Type:array)
        , focus VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO history (id, description, excluded, included, focus) SELECT id, description, excluded, included, focus FROM __temp__history');
        $this->addSql('DROP TABLE __temp__history');
    }
}
