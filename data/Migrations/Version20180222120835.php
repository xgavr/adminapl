<?php

namespace Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180222120835 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('price_gettings');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('ftp', 'string', ['notnull'=>false, 'length' => 512]);        
        $table->addColumn('ftp_login', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('ftp_password', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('email', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('email_password', 'string', ['notnull'=>false, 'length' => 64]);        
        $table->addColumn('link', 'string', ['notnull'=>false, 'length' => 512]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_price_getting_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('price_gettings');
    }
}
