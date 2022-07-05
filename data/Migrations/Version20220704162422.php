<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\PtSheduler;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220704162422 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('pt_sheduler');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office2_id', 'integer', ['notnull' => true]);
        $table->addColumn('generator_time', 'integer', ['notnull' => true]);
        $table->addColumn('generator_day', 'integer', ['notnull' => true]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => PtSheduler::STATUS_ACTIVE]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rel_sh_office_id_offcie_id_fk');
        $table->addForeignKeyConstraint('office', ['office2_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rel_sh_office2_id_offcie_id_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('pt_sheduler');
    }
}
