<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220621193834 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('register');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('doc_type', 'integer', ['notnull' => true]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['date_oper'], 'date_oper_indx');
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('register_variable');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_var', 'datetime', ['notnull'=>true]);
        $table->addColumn('var_type', 'integer', ['notnull' => true]);
        $table->addColumn('var_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('register');
        $schema->dropTable('register_variable');
    }
}
