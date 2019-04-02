<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\GenericGroup;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190402044204 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('generic_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('td_id', 'integer', ['notnull' => true]);
        $table->addColumn('apl_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => GenericGroup::STATUS_RETIRED]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('assembly_group', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('master_name', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('usage_name', 'string', ['notnull'=>false, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['td_id'], 'td_id_uindx');
        $table->addOption('engine' , 'InnoDB');  

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('generic_group');
    }
}
