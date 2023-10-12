<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231012042422 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('revision');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'revision_user_id_user_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->getTable('mutual');
        $table->addColumn('revision_id', 'integer', ['notnull'=>false]);        
        $table->addForeignKeyConstraint('revision', ['revision_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'mutual_revision_id_revision_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('mutual');
        $table->removeForeignKey('mutual_revision_id_revision_id_fk');
        $table->dropColumn('revision_id');
        
        $schema->dropTable('revision');
    }
}
