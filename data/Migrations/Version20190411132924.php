<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190411132924 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('apl_payment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('apl_payment_id', 'integer', ['notnull' => true]);
        $table->addColumn('apl_payment_date', 'datetime', ['notnull' => true]);
        $table->addColumn('apl_payment_sum', 'float', ['notnull' => true]);
        $table->addColumn('apl_payment_type', 'string', ['notnull' => true, 'lenght' => 16]);
        $table->addColumn('apl_payment_type_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['apl_payment_id'], 'apl_payment_id_uindx');
        $table->addOption('engine' , 'InnoDB');  
        
        $table = $schema->createTable('acquiring_apl_payment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('acquiring_id', 'integer', ['notnull'=>true]);
        $table->addColumn('apl_payment_id', 'integer', ['notnull'=>true]);
        $table->addIndex(['acquiring_id'], 'acquiring_id_indx');
        $table->addIndex(['apl_payment_id'], 'apl_payment_id_indx');
        $table->addForeignKeyConstraint('acquiring', ['acquiring_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'acq_id_acq_pay_acq_id_fk');
        $table->addForeignKeyConstraint('apl_payment', ['apl_payment_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'pay_id_acq_pay_pay_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');  
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('acquiring_apl_payment');
        $schema->dropTable('apl_payment');
    }
}
