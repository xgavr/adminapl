<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fasade\Entity\GroupSite;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209051332 extends AbstractMigration
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
    
    
    public function getDescription(): string
    {
        return 'Группы для сайта';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('group_site');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('group_site_id', 'integer', ['notnull'=>false, 'comment' => 'Родительская группа']);
        $table->addColumn('level', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Уровень']);
        $table->addColumn('sort', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Сортировка']);
        $table->addColumn('code', 'string', ['notnull'=>true, 'length' => 32, 'comment' => 'Полный код']);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 120, 'comment' => 'Наименование']);
        $table->addColumn('slug', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'ЧПУ']);
        $table->addColumn('description', 'text', ['notnull'=>false, 'comment' => 'Описание']);
        $table->addColumn('image', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Картинка']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => GroupSite::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('good_count', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Количество товаров']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'code_uindx');
        $table->addUniqueIndex(['sort', 'name'], 'sort_name_indx');
        $table->addForeignKeyConstraint('group_site', ['group_site_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'group_site_id_group_site_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('token_group');
        $table->addColumn('group_site_id', 'integer', ['notnull' => false, 'comment' => 'Категория для сайта']);
        $table->addForeignKeyConstraint('group_site', ['group_site_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'token_group_group_site_id_group_site_id_fk');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token_group');
        $table->removeForeignKey('token_group_group_site_id_group_site_id_fk');
        $table->dropColumn('group_site_id');
        
        $schema->dropTable('group_site');
    }
}
