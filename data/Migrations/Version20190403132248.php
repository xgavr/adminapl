<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403132248 extends AbstractMigration
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
        $table = $schema->createTable('attribute_value');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('td_id', 'integer', ['notnull' => true]);
        $table->addColumn('value', 'string', ['notnull'=>true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['td_id', 'value'], 'td_id_value_uindx');
        $table->addOption('engine' , 'InnoDB');  

        $table = $schema->createTable('attribute');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('td_id', 'integer', ['notnull' => true]);
        $table->addColumn('value_id', 'integer', ['notnull'=>true]);
        $table->addColumn('block_no', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('is_conditional', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('is_interval', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('is_linked', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('value_type', 'string', ['notnull'=>true, 'length' => 3]);
        $table->addColumn('value_unit', 'string', ['notnull'=>true, 'length' => 16]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'lenght' => 128]);
        $table->addColumn('short_name', 'string', ['notnull'=>true, 'lenght' => 128]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['td_id'], 'td_id_uindx');
        $table->addOption('engine' , 'InnoDB'); 
        
        $table = $schema->createTable('good_attribute_value');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('attribute_id', 'integer', ['notnull'=>true]);
        $table->addColumn('value_id', 'integer', ['notnull'=>true]);
        $table->addIndex(['good_id'], 'good_id_indx');
        $table->addIndex(['attribute_id'], 'attribute_id_indx');
        $table->addIndex(['value_id'], 'value_id_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'good_id_good_attr_val_good_id_fk');
        $table->addForeignKeyConstraint('attribute', ['attribute_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'attr_id_good_attr_val_attr_id_fk');
        $table->addForeignKeyConstraint('attribute_value', ['value_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'attr_value_id_good_attr_val_value_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');  
        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('good_attribute_value');
        $schema->dropTable('attribute_value');
        $schema->dropTable('attribute');

    }
}
