<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171218210344 extends AbstractMigration
{

    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {
        $description = 'Дополнение в таблицу client';
        return $description;
    }   
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->dropColumn('status');
        $table->dropColumn('date_created');
    }
}
