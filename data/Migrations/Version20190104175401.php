<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190104175401 extends AbstractMigration
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
        $table = $schema->createTable('token_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('lemms', 'string', ['notnull'=>true, 'length' => 256]);        
        $table->addColumn('ids', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('good_count', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['ids'], 'ids_indx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('goods');
        $table->addColumn('token_group_id', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addForeignKeyConstraint('token_group', ['token_group_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'token_group_goods_id_token_group_id_fk');
        
        $table = $schema->createTable('token_group_token');
        $table->addColumn('id', 'bigint', ['autoincrement'=>true]);
        $table->addColumn('token_group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('token_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('token', ['token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'token_group_token_token_id_token_id_fk');
        $table->addForeignKeyConstraint('token_group', ['token_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'token_group_token_token_group_id_token_group_id_fk');
        $table->addOption('engine' , 'InnoDB'); 

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('token_group_token');        
        
        $table = $schema->getTable('goods');
        $table->dropColumn('token_group_id');

        $schema->dropTable('token_group');        

    }
}
