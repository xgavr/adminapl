<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Admin\Entity\Setting;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190610085121 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('setting');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('controller', 'string', ['notnull' => true, 'lenght' => 256]);
        $table->addColumn('action', 'string', ['notnull' => true, 'lenght' => 128]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => Setting::STATUS_RETIRED]);
        $table->addColumn('last_mod', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['controller', 'action'], 'mca_uindx');
        $table->addOption('engine' , 'InnoDB');                  

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('setting');
    }
}
