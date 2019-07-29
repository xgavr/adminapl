<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190727163602 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('cross');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('filename', 'string', ['notnull'=>true, 'length'=>512]);
        $table->addColumn('row_count', 'integer', ['notnull'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_cross_supplier_id_fk');        
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('cross_list');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('cross_id', 'integer', ['notnull'=>true]);
        $table->addColumn('rawdata', 'text', ['notnull'=>true]);
        $table->addColumn('producer_name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('producer_article', 'string', ['notnull'=>true, 'length'=>32]);
        $table->addColumn('producer_article_name', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('brand_name', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('brand_article', 'string', ['notnull'=>true, 'length'=>32]);
        $table->addColumn('brand_article_name', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('article_id', 'integer', ['notnull'=>false]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['cross_id'], 'cross_id');
        $table->addIndex(['article_id'], 'article_id');
        $table->addForeignKeyConstraint('cross', ['cross_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cross_id_cross_list_cross_id_fk');                
        $table->addForeignKeyConstraint('article', ['article_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'article_id_cross_list_article_id_fk');                
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('cross_list');
        $schema->dropTable('cross');
    }
}
