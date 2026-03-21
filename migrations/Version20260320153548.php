<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320153548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building ADD subtree_image_count INT NOT NULL');
        $this->addSql('ALTER TABLE file ADD subtree_image_count INT NOT NULL');
        $this->addSql('ALTER TABLE location ADD subtree_image_count INT NOT NULL');
        $this->addSql('ALTER TABLE topic ADD subtree_image_count INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building DROP subtree_image_count');
        $this->addSql('ALTER TABLE file DROP subtree_image_count');
        $this->addSql('ALTER TABLE location DROP subtree_image_count');
        $this->addSql('ALTER TABLE topic DROP subtree_image_count');
    }
}
