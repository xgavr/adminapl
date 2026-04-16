<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Mark;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415154501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('marks');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'comment' => 'Код записи в апл']);
        $table->addColumn('good_id', 'integer', ['notnull'=>true, 'comment' => 'Товар']);        
        $table->addColumn('order_id', 'integer', ['notnull'=>true, 'comment' => 'Заказ']);        
        $table->addColumn('mark', 'string', ['notnull'=>true, 'length' => 246, 'comment' => 'Код маркировки']);        
        $table->addColumn('mark_group', 'integer', ['notnull'=>true, 'comment' => 'Группа маркировки']);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => Mark::STATUS_ACTIVE, 'comment' => 'Статус']);   
        $table->addColumn('mark_status', 'integer', ['notnull'=>true,'default' => Mark::MARK_UNKNOWN, 'comment' => 'Статус в ЧЗ']);   
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_updated', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        
        $table->addUniqueIndex(['mark'], 'mark_uindx');
        $table->addUniqueIndex(['apl_id'], 'apl_id_uindx');
        $table->addIndex(['order_id', 'good_id'], 'order_id_good_id_indx');
        
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('marks');
    }
}
