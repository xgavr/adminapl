<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180702064513 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->addColumn('iid', 'string', ['length' => 64, 'notnull'=>FALSE]);        
        $table->addColumn('oem', 'string', ['length' => 32, 'notnull'=>FALSE]);        
        $table->addColumn('vendor', 'string', ['length' => 32, 'notnull'=>FALSE]);        
        $table->addColumn('lot', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('unit', 'string', ['length' => 8, 'notnull'=>FALSE]);        
        $table->addColumn('car', 'string', ['length' => 256, 'notnull'=>FALSE]);        
        $table->addColumn('bar', 'string', ['length' => 32, 'notnull'=>FALSE]);        
        $table->addColumn('currency', 'string', ['length' => 8, 'notnull'=>FALSE]);        
        $table->addColumn('comment', 'string', ['length' => 256, 'notnull'=>FALSE]);        
        $table->addColumn('weight', 'float', ['notnull'=>FALSE]);        
        $table->addColumn('country', 'string', ['length' => 64, 'notnull'=>FALSE]);        
        $table->addColumn('markdown', 'integer', ['notnull'=>false]);        
        $table->addColumn('image', 'string', ['length' => 256, 'notnull'=>false]);        
        $table->addColumn('sale', 'integer', ['notnull'=>false]);

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->dropColumn('iid');        
        $table->dropColumn('oem');        
        $table->dropColumn('vendor');        
        $table->dropColumn('lot');        
        $table->dropColumn('unit');        
        $table->dropColumn('car');        
        $table->dropColumn('bar');        
        $table->dropColumn('currency');        
        $table->dropColumn('comment');        
        $table->dropColumn('weight');        
        $table->dropColumn('country');        
        $table->dropColumn('markdown');        
        $table->dropColumn('image');        
        $table->dropColumn('sale');        

    }
}
