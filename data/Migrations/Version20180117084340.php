<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180117084340 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->addColumn('manager_id', 'integer', ['notnull'=>true]);
        $table->addForeignKeyConstraint('user', ['manager_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'user_id_client_manager_id_fk');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->dropColumn('manager_id');

    }
}
