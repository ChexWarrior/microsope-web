<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115183245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "action" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, history_id INTEGER NOT NULL, user_id INTEGER NOT NULL, operation VARCHAR(255) NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INTEGER NOT NULL, description VARCHAR(1000) NOT NULL, CONSTRAINT FK_47CC8C921E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_47CC8C92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_47CC8C921E058452 ON "action" (history_id)');
        $this->addSql('CREATE INDEX IDX_47CC8C92A76ED395 ON "action" (user_id)');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, period_id INTEGER NOT NULL, created_by_id INTEGER NOT NULL, place INTEGER NOT NULL, tone VARCHAR(255) NOT NULL, description VARCHAR(1000) NOT NULL, CONSTRAINT FK_3BAE0AA7EC8B7ADE FOREIGN KEY (period_id) REFERENCES period (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3BAE0AA7B03A8386 FOREIGN KEY (created_by_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7EC8B7ADE ON event (period_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B03A8386 ON event (created_by_id)');
        $this->addSql('CREATE TABLE history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, excluded CLOB DEFAULT NULL --(DC2Type:array)
        , included CLOB DEFAULT NULL --(DC2Type:array)
        , focus VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE period (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, history_id INTEGER NOT NULL, created_by_id INTEGER NOT NULL, place INTEGER NOT NULL, tone VARCHAR(255) NOT NULL, description VARCHAR(1000) NOT NULL, CONSTRAINT FK_C5B81ECE1E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C5B81ECEB03A8386 FOREIGN KEY (created_by_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C5B81ECE1E058452 ON period (history_id)');
        $this->addSql('CREATE INDEX IDX_C5B81ECEB03A8386 ON period (created_by_id)');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, history_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, legacy VARCHAR(255) DEFAULT NULL, lens BOOLEAN NOT NULL, CONSTRAINT FK_98197A651E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_98197A651E058452 ON player (history_id)');
        $this->addSql('CREATE TABLE scene (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER NOT NULL, created_by_id INTEGER NOT NULL, place INTEGER NOT NULL, tone VARCHAR(255) NOT NULL, description VARCHAR(1000) NOT NULL, CONSTRAINT FK_D979EFDA71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D979EFDAB03A8386 FOREIGN KEY (created_by_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D979EFDA71F7E88B ON scene (event_id)');
        $this->addSql('CREATE INDEX IDX_D979EFDAB03A8386 ON scene (created_by_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "action"');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE history');
        $this->addSql('DROP TABLE period');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE scene');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
