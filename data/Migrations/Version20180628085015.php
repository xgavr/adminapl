<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180628085015 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_description');
        $table->addColumn('oem', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('vendor', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('lot', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('unit', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('car', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('bar', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('currency', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('comment', 'integer', ['notnull'=>FALSE]);        
        $table->addColumn('weight', 'integer', ['notnull'=>FALSE]);        

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_description');
        $table->dropColumn('oem');        
        $table->dropColumn('vendor');        
        $table->dropColumn('lot');        
        $table->dropColumn('unit');        
        $table->dropColumn('car');        
        $table->dropColumn('bar');        
        $table->dropColumn('currency');        
        $table->dropColumn('comment');        
        $table->dropColumn('weight');        

    }
}
