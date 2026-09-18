<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260918213713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add subtree image counts to tree entities, initializing existing rows to zero.';
    }

    public function up(Schema $schema): void
    {
        foreach (['building', 'file', 'location', 'topic'] as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ADD subtree_image_count INT DEFAULT 0 NOT NULL', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER subtree_image_count DROP DEFAULT', $table));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['building', 'file', 'location', 'topic'] as $table) {
            $this->addSql(sprintf('ALTER TABLE %s DROP subtree_image_count', $table));
        }
    }
}
