<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181112154946 extends AbstractMigration
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
        $table = $schema->createTable('name_raw');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('lemma', 'string', ['notnull'=>true, 'length' => 24]);        
        $table->addColumn('stemm', 'string', ['notnull'=>true, 'length' => 24]);        
        $table->addColumn('pos', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->addColumn('article_id', 'integer', ['notnull' => true]);
        $table->addForeignKeyConstraint('article', ['article_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'article_id_name_raw_article_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['article_id', 'lemma', 'stemm'], 'lemma_article_id');
        $table->addOption('engine' , 'InnoDB');


        $table = $schema->createTable('rawprice_name_raw');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('rawprice_id', 'bigint', ['notnull'=>true]);
        $table->addColumn('name_raw_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('name_raw', ['name_raw_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rawprice_name_raw_name_raw_id_name_raw_id_fk');
        $table->addForeignKeyConstraint('rawprice', ['rawprice_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rawprice_name_raw_rawprice_id_rawprice_id_fk');
        $table->addOption('engine' , 'InnoDB'); 
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('rawprice_name_raw');        
        $schema->dropTable('name_raw');        
    }
}
