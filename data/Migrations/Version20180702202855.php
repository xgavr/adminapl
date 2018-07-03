<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180702202855 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->addColumn('status', 'integer', ['default' => 1, 'notnull'=>true]);        
        $table->addColumn('oem_brand', 'string', ['length' => 128, 'notnull'=>false]);        
     
        $table = $schema->getTable('price_description');
        $table->addColumn('oem_brand', 'integer', ['notnull'=>false]);        

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->dropColumn('status');        
        $table->dropColumn('oem_brand');        

        $table = $schema->getTable('price_description');
        $table->dropColumn('oem_brand');        

    }
}
