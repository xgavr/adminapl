<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830140442 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('article');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('code', 'string', ['notnull'=>true, 'length' => 24]);        
        $table->addColumn('fullcode', 'string', ['notnull'=>true, 'length' => 36]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'code_idx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('rawprice');
        $table->addColumn('article_id', 'integer', ['notnull' => false]);
        $table->addIndex(['status'], 'status_idx'); 
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('article');

        $table = $schema->getTable('rawprice');
        $table->dropIndex('status_idx');
        $table->dropColumn('article_id');
    }
}
