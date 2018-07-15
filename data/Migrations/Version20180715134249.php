<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180715134249 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->changeColumn('lot', ['type' => Type::getType('string'), 'length' => 32]);
        $table->changeColumn('weight', ['type' => Type::getType('string'), 'length' => 64]);
        $table->changeColumn('markdown', ['type' => Type::getType('string'), 'length' => 64]);
        $table->changeColumn('sale', ['type' => Type::getType('string'), 'length' => 64]);
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
