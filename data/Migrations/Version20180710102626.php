<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180710102626 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_gettings');
        $table->addColumn('ftp_dir', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('filename', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('status_filename', 'integer', ['notnull'=>true, 'default' => 1]);        

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_gettings');
        $table->dropColumn('ftp_dir');        
        $table->dropColumn('filename');        
        $table->dropColumn('status_filename');        

    }
}
