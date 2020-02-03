<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200203173621 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fp_tree');
        $table->dropIndex('rtt_uindx');
        $table->addColumn('parent_tree_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addUniqueIndex(['parent_tree_id', 'token_id'], 'rtt_uindx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fp_tree');
        $table->dropIndex('rtt_uindx');
        $table->dropColumn('parent_tree_id');
        $table->addUniqueIndex(['root_tree_id', 'root_token_id', 'token_id'], 'rtt_uindx');
    }
}
