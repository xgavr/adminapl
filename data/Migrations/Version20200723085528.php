<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200723085528 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('log');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('log_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('message', 'json', ['notnull'=>false]);
        $table->addColumn('priority', 'integer', ['notnull'=>true]);
        $table->addColumn('user_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['log_key'], 'log_key_idx');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_log_user_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('log');

    }
}
