<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180630202103 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_description');
        $table->addColumn('type', 'integer', ['notnull'=>true]);        
        $table->addColumn('markdown', 'integer', ['notnull'=>false]);        
        $table->addColumn('image', 'integer', ['notnull'=>false]);        
        $table->addColumn('sale', 'integer', ['notnull'=>false]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_description');
        $table->dropColumn('type');        
        $table->dropColumn('markdown');        
        $table->dropColumn('image');        
        $table->dropColumn('sale');        
    }
}
