<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Stock\Entity\Comiss;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527041125 extends AbstractMigration
{
    /**
     * @param boolean $enabled
     */
    protected function setForeignKeyChecks($enabled)
    {
        $connection = $this->connection;
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $connection->exec(sprintf('SET foreign_key_checks = %s;', (int)$enabled));
        }
    }

    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema)
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema)
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ot');
        $table->removeForeignKey('user_id_ot_comiss_id_fk');
        $table->addForeignKeyConstraint('contact', ['comiss_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_ot_comiss_id_fk');        

        $table = $schema->dropTable('comiss');

        $table = $schema->createTable('comiss');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Comiss::STATUS_ACTIVE]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
        $table->addIndex(['date_oper'], 'date_oper_idx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_comiss_good_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_comiss_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_comiss_company_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_comiss_contact_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ot');
        $table->removeForeignKey('contact_id_ot_comiss_id_fk');
        $table->addForeignKeyConstraint('user', ['comiss_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_ot_comiss_id_fk');        

        $table = $schema->dropTable('comiss');
        $table = $schema->createTable('comiss');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Comiss::STATUS_ACTIVE]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
        $table->addIndex(['date_oper'], 'date_oper_idx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_comiss_good_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_comiss_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_comiss_company_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_comiss_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }
}
