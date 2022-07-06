<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220706151608 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('register');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true]);
        $table->addUniqueIndex(['doc_stamp'], 'doc_stamp_uindx');

        $table = $schema->getTable('register_variable');
        $table->addColumn('var_stamp', 'float', ['notnull' => true]);

        $table = $schema->getTable('movement');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('register');
        $table->dropIndex('doc_stamp_uindx');
        $table->dropColumn('doc_stamp');

        $table = $schema->getTable('register_variable');
        $table->dropColumn('var_stamp');

        $table = $schema->getTable('movement');
        $table->dropIndex('doc_stamp_indx');
        $table->dropColumn('doc_stamp');
    }
}
