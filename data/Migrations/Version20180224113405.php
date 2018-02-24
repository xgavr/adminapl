<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180224113405 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('request_setting');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 64]);        
        $table->addColumn('site', 'string', ['notnull'=>false, 'length' => 256]);        
        $table->addColumn('login', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('password', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('description', 'string', ['notnull'=>false, 'length' => 1024]);        
        $table->addColumn('mode', 'integer', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_request_setting_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('request_setting');

    }
}
