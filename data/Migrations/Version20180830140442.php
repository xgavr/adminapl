<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830140442 extends AbstractMigration
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
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('article');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>false]);        
        $table->addColumn('code', 'string', ['notnull'=>true, 'length' => 24]);        
        $table->addColumn('fullcode', 'string', ['notnull'=>true, 'length' => 36]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'code_idx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'goods_id_article_good_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        
        $table = $schema->getTable('rawprice');
        $table->addColumn('article_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('article', ['article_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'article_id_rawprice_article_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('article');

        $table = $schema->getTable('rawprice');
        $table->dropColumn('article_id');
    }
}
