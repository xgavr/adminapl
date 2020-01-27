<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200127073123 extends AbstractMigration
{
        
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('fp_tree');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('root_tree_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('root_token_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('token_id', 'integer', ['notnull'=>true]);
        $table->addColumn('frequency', 'integer', ['notnull'=>true, 'default' => 0]); //счетчик
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_tree_id', 'root_token_id', 'token_id'], 'rtt_uindx');
        $table->addForeignKeyConstraint('token', ['token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fp_tree_token_id_token_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fp_tree');
        $table->removeForeignKey('fp_tree_token_id_token_id_fk');
        $schema->dropTable('fp_tree');
    }
}
