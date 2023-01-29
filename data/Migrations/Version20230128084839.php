<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230128084839 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('edo_operator');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('code', 'string', ['notnull'=>true, 'length'=>24]);
        $table->addColumn('inn', 'string', ['notnull'=>true, 'length'=>12]);
        $table->addColumn('site', 'string', ['notnull'=>false, 'length'=>1024]);
        $table->addColumn('info', 'text', ['notnull'=>false]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('legal');
        $table->addColumn('edo_operator_id', 'integer', ['notnull' => false]);
        $table->addColumn('edo_address', 'string', ['notnull' => false]);
        $table->addForeignKeyConstraint('edo_operator', ['edo_operator_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'legal_edo_id_edo_operator_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->removeForeignKey('legal_edo_id_edo_operator_id_fk');
        $table->dropColumn('edo_operator_id');
        $table->dropColumn('edo_address');
        
        $schema->dropTable('edo_operator');
    }
}
