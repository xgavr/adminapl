<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220914101916 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('good_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull' => true]);
        $table->addColumn('company_id', 'integer', ['notnull' => true]);
        $table->addColumn('base_stamp', 'float', ['notnull' => false]);
        $table->addColumn('rest', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('reserve', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('delivery', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('vozvrat', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('price', 'float', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['good_id', 'office_id', 'company_id'], 'good_off_com_uindx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'gd_good_id_good_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'gd_office_id_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'gd_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('register', ['base_stamp'], ['doc_stamp'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'gd_base_stamp_register_doc_stamp_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('good_balance');
    }
}
