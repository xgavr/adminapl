<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240405082917 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->addColumn('order_count', 'integer', ['notnull'=>true, 'default' => 0, 'comment' => 'Количество заказов']);
        $table->addColumn('avg_bill', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Средний чек']);        
        $table->addColumn('new_client_count', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Количество новых клиентов']);        
        $table->addColumn('cpo', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Стоимость нового заказа']);        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->dropColumn('order_count');
        $table->dropColumn('avg_bill');
        $table->dropColumn('new_client_count');
        $table->dropColumn('cpo');
    }
}
