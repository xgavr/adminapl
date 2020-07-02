<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\PtuGood;
use Stock\Entity\Ptu;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200630193856 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('ntd');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('ntd', 'string', ['notnull'=>true, 'length'=>64]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['ntd'], 'ntd_uidx');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('unit');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>16]);
        $table->addColumn('code', 'string', ['notnull'=>true, 'length'=>16]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code', 'name'], 'code_name_uidx');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('ptu');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>false]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Ptu::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> Ptu::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> Ptu::STATUS_EX_NEW]);
        $table->addColumn('number_doc', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('date_doc', 'date', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>true]);
        $table->addColumn('contract_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_ptu_legal_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contract_id_ptu_contract_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_ptu_office_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('ptu_good');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> PtuGood::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> PtuGood::STATUS_DOC_NOT_RECD]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('ptu_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('country_id', 'integer', ['notnull'=>false]);
        $table->addColumn('unit_id', 'integer', ['notnull'=>false]);
        $table->addColumn('ntd_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('ptu', ['ptu_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'ptu_id_ptu_good_ptu_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_ptu_good_good_id_fk');
        $table->addForeignKeyConstraint('country', ['country_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'country_id_ptu_good_country_id_fk');
        $table->addForeignKeyConstraint('unit', ['unit_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'unit_id_ptu_good_unit_id_fk');
        $table->addForeignKeyConstraint('ntd', ['ntd_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'ntd_id_ptu_good_ntd_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('ntd');
        $schema->dropTable('unit');
        $schema->dropTable('ptu');
        $schema->dropTable('ptu_good');
    }
}
