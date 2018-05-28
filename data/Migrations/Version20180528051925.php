<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180528051925 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('address');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>false, 'length'=>128]);        
        $table->addColumn('address', 'string', ['notnull'=>true, 'length'=>1024]);
        $table->addColumn('address_sms', 'string', ['notnull'=>false, 'length'=>256]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_address_contact_id_fk');        
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('messenger');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('type', 'integer', ['notnull'=>true]);
        $table->addColumn('ident', 'string', ['notnull'=>false, 'length'=>128]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_messenger_contact_id_fk');        
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('address');
        $schema->dropTable('messenger');

    }
}
