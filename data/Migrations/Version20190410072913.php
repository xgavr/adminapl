<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190410072913 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('acquiring');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('inn', 'string', ['notnull' => true, 'lenght' => 12]);
        $table->addColumn('point', 'string', ['notnull' => true, 'lenght' => 16]);
        $table->addColumn('cart', 'string', ['notnull' => true, 'lenght' => 20]);
        $table->addColumn('acode', 'string', ['notnull' => true, 'lenght' => 20]);
        $table->addColumn('cart_type', 'string', ['notnull' => true, 'lenght' => 24]);
        $table->addColumn('amount', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('comiss', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('output', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('oper_type', 'string', ['notnull'=>true, 'length' => 24]);
        $table->addColumn('oper_date', 'date', ['notnull'=>true]);
        $table->addColumn('trans_date', 'datetime', ['notnull'=>true]);
        $table->addColumn('rrn', 'string', ['notnull'=>true, 'length' => 32]);
        $table->addColumn('ident', 'string', ['notnull'=>false, 'length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');  

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('acquiring');

    }
}
