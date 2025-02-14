<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fasade\Entity\GroupSite;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211162811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->addColumn('has_child', 'integer', ['notnull' => true, 'default' => GroupSite::HAS_NO_CHILD, 'comment' => 'Имеет подгруппу']);
        $table->addColumn('full_name', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Полное наименование']);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->dropColumn('has_child');
        $table->dropColumn('full_name');
    }
}
