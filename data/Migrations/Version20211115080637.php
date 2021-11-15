<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211115080637 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('good_supplier');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);
        $table->addColumn('rest', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('up_date', 'date', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['up_date'], 'update_indx');
        $table->addIndex(['good_id', 'supplier_id'], 'good_supplier_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_good_supplier_id_good_id_fk');
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_good_supplier_id_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('good_supplier');
    }
}
