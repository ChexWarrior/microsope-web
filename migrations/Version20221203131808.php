<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221203131808 extends AbstractMigration
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
        $this->addSql('CREATE TABLE history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, excluded CLOB DEFAULT NULL --(DC2Type:array)
        , included CLOB DEFAULT NULL --(DC2Type:array)
        , focus VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO history (id, description, excluded, included, focus) SELECT id, description, excluded, included, focus FROM __temp__history');
        $this->addSql('DROP TABLE __temp__history');
        $this->addSql('ALTER TABLE player ADD COLUMN lens BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__history AS SELECT id, description, excluded, included, focus FROM history');
        $this->addSql('DROP TABLE history');
        $this->addSql('CREATE TABLE history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, lens_id INTEGER DEFAULT NULL, description VARCHAR(255) NOT NULL, excluded CLOB DEFAULT NULL --(DC2Type:array)
        , included CLOB DEFAULT NULL --(DC2Type:array)
        , focus VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_27BA704B4FCBBD7A FOREIGN KEY (lens_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO history (id, description, excluded, included, focus) SELECT id, description, excluded, included, focus FROM __temp__history');
        $this->addSql('DROP TABLE __temp__history');
        $this->addSql('CREATE INDEX IDX_27BA704B4FCBBD7A ON history (lens_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, history_id, name, active, legacy FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, history_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, legacy VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_98197A651E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO player (id, history_id, name, active, legacy) SELECT id, history_id, name, active, legacy FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
        $this->addSql('CREATE INDEX IDX_98197A651E058452 ON player (history_id)');
    }
}
