<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180529171718 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->dropColumn('contact_id');
        $table->changeColumn('full_name', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->changeColumn('full_name', ['notnull' => true]);

    }
}
