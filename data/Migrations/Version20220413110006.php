<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220413110006 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('supplier', ['parent_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_parent_id_supplier_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->removeForeignKey('supplier_parent_id_supplier_id_fk');
        $table->dropColumn('parent_id');
    }
}
