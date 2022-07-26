<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220726155128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE building_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE location_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE building (id INT NOT NULL, user_id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E16F61D4A76ED395 ON building (user_id)');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, parent_id INT DEFAULT NULL, root_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(255) DEFAULT NULL, is_dir BOOLEAN NOT NULL, child_count INT NOT NULL, lvl INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F3610727ACA70 ON file (parent_id)');
        $this->addSql('CREATE INDEX IDX_8C9F361079066886 ON file (root_id)');
        $this->addSql('CREATE TABLE location (id INT NOT NULL, root_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, building_id INT NOT NULL, code VARCHAR(32) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, order_idx INT DEFAULT NULL, name VARCHAR(80) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E9E89CB79066886 ON location (root_id)');
        $this->addSql('CREATE INDEX IDX_5E9E89CB727ACA70 ON location (parent_id)');
        $this->addSql('CREATE INDEX IDX_5E9E89CB4D2A7E12 ON location (building_id)');
        $this->addSql('CREATE TABLE topic (code VARCHAR(10) NOT NULL, parent_id VARCHAR(10) DEFAULT NULL, root_id VARCHAR(10) DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, child_count INT NOT NULL, lvl INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX IDX_9D40DE1B727ACA70 ON topic (parent_id)');
        $this->addSql('CREATE INDEX IDX_9D40DE1B79066886 ON topic (root_id)');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610727ACA70 FOREIGN KEY (parent_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F361079066886 FOREIGN KEY (root_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB79066886 FOREIGN KEY (root_id) REFERENCES location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B727ACA70 FOREIGN KEY (parent_id) REFERENCES topic (code) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B79066886 FOREIGN KEY (root_id) REFERENCES topic (code) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB4D2A7E12');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610727ACA70');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F361079066886');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB79066886');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB727ACA70');
        $this->addSql('ALTER TABLE topic DROP CONSTRAINT FK_9D40DE1B727ACA70');
        $this->addSql('ALTER TABLE topic DROP CONSTRAINT FK_9D40DE1B79066886');
        $this->addSql('ALTER TABLE building DROP CONSTRAINT FK_E16F61D4A76ED395');
        $this->addSql('DROP SEQUENCE building_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE location_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE users');
    }
}
