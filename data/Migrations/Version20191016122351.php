<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191016122351 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('auto_db_responce');
        $table->addColumn('id', 'bigint', ['autoincrement'=>true]);
        $table->addColumn('uri_md5', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('uri', 'text', ['notnull'=>true]);
        $table->addColumn('response_md5', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('response', 'text', ['notnull'=>true]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['uri_md5'], 'auto_db_uri_md5_uindx');
        $table->addOption('engine' , 'InnoDB'); 
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('auto_db_responce');
    }
}
