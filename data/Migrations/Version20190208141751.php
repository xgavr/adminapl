<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190208141751 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('model');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('make_id', 'integer', ['notnull' => true]);
        $table->addColumn('td_id', 'integer', ['notnull' => true]);
        $table->addColumn('apl_id', 'integer', ['notnull' => true]);
        $table->addColumn('passenger', 'integer', ['notnull' => true]);
        $table->addColumn('commerc', 'integer', ['notnull' => true]);
        $table->addColumn('moto', 'integer', ['notnull' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('fullname', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('constructioninterval', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['td_id'], 'td_id_uindx');
        $table->addIndex(['apl_id'], 'apl_id_indx');
        $table->addForeignKeyConstraint('make', ['make_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'make_id_model_make_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('model');
    }
}
