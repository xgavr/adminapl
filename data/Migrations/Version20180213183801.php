<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180213183801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('region');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>10]);
        $table->addColumn('full_name', 'string', ['notnull'=>true, 'length'=>512]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('office');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);        
        $table->addColumn('region_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('full_name', 'string', ['notnull'=>true, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addForeignKeyConstraint('region', ['region_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'office_region_id_region_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->getTable('contact');
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        
        $table = $schema->createTable('legal');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('full_name', 'string', ['notnull'=>true, 'length'=>512]);
        $table->addColumn('inn', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('kpp', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('ogrn', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('okpo', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('head', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('address', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('info', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('chief_account', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_start', 'date', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
//        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
//                ['onUpdate'=>'CASCADE'], 'legal_contact_id_contact_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('bank_account');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('legal_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);
        $table->addColumn('city', 'string', ['notnull'=>true, 'length'=>256]);
        $table->addColumn('bik', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('ks', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('rs', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'bank_account_legal_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('contract');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('legal_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);
        $table->addColumn('act', 'string', ['notnull'=>true, 'length'=>256]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_start', 'date', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contract_legal_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('contract');
        $schema->dropTable('bank_account');
        $schema->dropTable('legal');

        $table = $schema->getTable('contact');
        $table->dropColumn('office_id');
        
        $schema->dropTable('office');
        $schema->dropTable('region');
        
    }
}
