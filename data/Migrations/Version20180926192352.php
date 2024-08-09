<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180926192352 extends AbstractMigration
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
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema): void
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema): void
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }
    
    public function up(Schema $schema) : void
    {
        
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('oem_raw');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('code', 'string', ['notnull'=>true, 'length' => 24]);        
        $table->addColumn('fullcode', 'string', ['notnull'=>true, 'length' => 36]);        
        $table->addColumn('article_id', 'integer', ['notnull' => true]);
        $table->addForeignKeyConstraint('article', ['article_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'article_id_oem_raw_article_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code', 'article_id'], 'code_article_id');
        $table->addOption('engine' , 'InnoDB');


        $table = $schema->createTable('rawprice_oem_raw');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('rawprice_id', 'bigint', ['notnull'=>true]);
        $table->addColumn('oem_raw_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('oem_raw', ['oem_raw_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rawprice_oem_raw_oem_raw_id_oem_raw_id_fk');
        $table->addForeignKeyConstraint('rawprice', ['rawprice_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rawprice_oem_raw_rawprice_id_rawprice_id_fk');
        $table->addOption('engine' , 'InnoDB'); 
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('rawprice_oem_raw');        
        $schema->dropTable('oem_raw');        
    }
}
