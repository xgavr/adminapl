<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231207082052 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('search_log');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('content', 'string', ['notnull'=>true, 'length' => 256, 'comment' => 'Поисковый запрос']);
        $table->addColumn('ip_address', 'string', ['notnull'=>false, 'length' => 36, 'comment' => 'Ip адрес клиента']);
        $table->addColumn('device', 'string', ['notnull'=>false, 'length' => 256, 'comment' => 'Устройство клиента']);
        $table->addColumn('found', 'integer', ['notnull'=>true, 'default' => 0, 'comment' => 'Найдено записей']);
        $table->addColumn('search_title_id', 'integer', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('search_title', ['search_title_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'search_log_search_title_id_search_title_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('search_log');
    }
}
