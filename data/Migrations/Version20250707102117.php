<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707102117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->addColumn('rating', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('rating_count', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('total_score', 'integer', ['notnull' => true, 'default' => 0]);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->dropColumn('rating');
        $table->dropColumn('rating_count');
        $table->dropColumn('total_score');
    }
}
