<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171219081336 extends AbstractMigration
{
    
    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {
        $description = 'Создание таблицы контактов';
        return $description;
    }    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('contact');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('user_id', 'integer', ['notnull'=>false, 'default' => -1]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false, 'default' => -1]);
        $table->addColumn('client_id', 'integer', ['notnull'=>false, 'default' => -1]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('contact');

    }
}
