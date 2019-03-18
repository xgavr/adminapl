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
        $table->addColumn('producer_id', 'integer', ['notnull'=>true]);
        $table->addIndex(['good_id'], 'good_id_indx');
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
