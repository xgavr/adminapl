<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180908052834 extends AbstractMigration
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
        $table = $schema->getTable('raw');
        $table->addColumn('parse_stage', 'integer', ['notnull' => true, 'default' => 1]);
    
        $table = $schema->getTable('article');
        $table->dropIndex('code_idx');
        $table->changeColumn('good_id', ['notnull' => false]);
        $table->addColumn('unknown_producer_id', 'integer', ['notnull' => true]);
        $table->addForeignKeyConstraint('unknown_producer', ['unknown_producer_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'unknown_producer_id_article_unknown_producer_id_fk');
        $table->addUniqueIndex(['code', 'unknown_producer_id'], 'code_unknown_producer_id');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('raw');
        $table->dropColumn('parse_stage');
        
        $table = $schema->getTable('article');
        $table->dropColumn('unknown_producer_id');        
        $table->addUniqueIndex(['code'], 'code_idx');
    }
}
