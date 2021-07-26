<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Ring;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210726085226 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('ring_help_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('mode', 'integer', ['notnull'=>true, 'default'=>Ring::MODE_NEW_ORDER]);
        $table->addColumn('sort', 'integer', ['notnull'=>true, 'default'=>100]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('info', 'string', ['notnull'=>false, 'length' => 512]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['mode'], 'mode_idx');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('ring_help');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('mode', 'integer', ['notnull'=>true, 'default'=>Ring::MODE_NEW_ORDER]);
        $table->addColumn('sort', 'integer', ['notnull'=>true, 'default'=>100]);
        $table->addColumn('ring_help_group_id', 'integer', ['notnull'=>false]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 256]);
        $table->addColumn('info', 'string', ['notnull'=>false, 'length' => 512]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['mode'], 'mode_idx');
        $table->addForeignKeyConstraint('ring_help_group', ['ring_help_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rhg_id_ring_help_rhg_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('ring_help');
        $schema->dropTable('ring_help_group');
    }
}
