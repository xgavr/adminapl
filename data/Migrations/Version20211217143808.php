<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cash\Entity\Cash;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211217143808 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('cash');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('commission', 'float', ['notnull'=>true, 'default' => 0.0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Cash::STATUS_ACTIVE]);
        $table->addColumn('rest_status', 'integer', ['notnull'=>true, 'default'=> Cash::REST_RETIRED]);
        $table->addColumn('till_status', 'integer', ['notnull'=>true, 'default'=> Cash::TILL_RETIRED]);
        $table->addColumn('order_status', 'integer', ['notnull'=>true, 'default'=> Cash::ORDER_RETIRED]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_cash_office_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('cash');
    }
}
