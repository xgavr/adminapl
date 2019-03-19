<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318084416 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('oem');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('oe', 'string', ['notnull'=>true, 'length' => 24]);
        $table->addColumn('oe_number', 'string', ['notnull'=>true, 'length' => 36]);
        $table->addColumn('brand_name', 'string', ['notnull'=>true, 'length' => 64]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addColumn('source', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addUniqueIndex(['oe', 'good_id'], 'oe_number_good_id_uindx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'good_id_oem_good_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');  
        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('oem');
    }
}
