<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201193415 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('statement_token');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('lemma', 'string', ['notnull'=>true, 'length' => 64]);        
        $table->addColumn('correct', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => 1]);        
        $table->addColumn('frequency', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->addColumn('idf', 'float', ['notnull'=>true, 'default' => 0]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['lemma'], 'lemma_indx');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('statement_token_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('lemms', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('ids', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('statement_count', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->setPrimaryKey(['id']);
        $table->addIndex(['ids'], 'ids_indx');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('statement_token');        
        $schema->dropTable('statement_token_group');        
    }
}
