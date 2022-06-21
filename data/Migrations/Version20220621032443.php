<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Order;
use Application\Entity\Bid;
use Stock\Entity\Vt;
use Stock\Entity\VtGood;
use Stock\Entity\St;
use Stock\Entity\StGood;
use Stock\Entity\Pt;
use Stock\Entity\PtGood;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220621032443 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('retail_count', 'integer', ['notnull' => true, 'default' => 0]);

        $table = $schema->getTable('movement');
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('base_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('base_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');
        $table->addIndex(['base_type', 'base_id'], 'base_indx');

        $table = $schema->getTable('mutual');
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');

        $table = $schema->getTable('retail');
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');
        
        $table = $schema->getTable('comiss');
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');

        $table = $schema->getTable('orders');
        $table->addColumn('status_ex', 'integer', ['notnull' => true, 'default' => Order::STATUS_EX_NO]);
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Order::STATUS_ACCOUNT_NO]);
        $table->addIndex(['status_ex'], 'status_ex_indx');
        $table->addIndex(['status_account'], 'status_account_indx');

        $table = $schema->getTable('bid');
        $table->addColumn('take', 'integer', ['notnull' => true, 'default' => Bid::TAKE_NO]);
        $table->addIndex(['take'], 'take_indx');

        $table = $schema->getTable('vt');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Vt::STATUS_ACCOUNT_NO]);
        $table->addIndex(['status_account'], 'status_account_indx');

        $table = $schema->getTable('vt_good');
        $table->addColumn('take', 'integer', ['notnull' => true, 'default' => VtGood::TAKE_NO]);
        $table->addIndex(['take'], 'take_indx');

        $table = $schema->getTable('st');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => St::STATUS_ACCOUNT_NO]);
        $table->addIndex(['status_account'], 'status_account_indx');

        $table = $schema->getTable('st_good');
        $table->addColumn('take', 'integer', ['notnull' => true, 'default' => StGood::TAKE_NO]);
        $table->addIndex(['take'], 'take_indx');

        $table = $schema->getTable('pt');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Pt::STATUS_ACCOUNT_NO]);
        $table->addIndex(['status_account'], 'status_account_indx');

        $table = $schema->getTable('pt_good');
        $table->addColumn('take', 'integer', ['notnull' => true, 'default' => PtGood::TAKE_NO]);
        $table->addIndex(['take'], 'take_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('retail_count', 'integer', ['notnull' => true, 'default' => 0]);

        $table = $schema->getTable('movement');
        $table->dropIndex('doc_indx');
        $table->dropIndex('base_indx');
        $table->dropColumn('doc_type');
        $table->dropColumn('doc_id');
        $table->dropColumn('base_type');
        $table->dropColumn('base_id');

        $table = $schema->getTable('mutual');
        $table->dropIndex('doc_indx');
        $table->dropColumn('doc_type');
        $table->dropColumn('doc_id');

        $table = $schema->getTable('retail');
        $table->dropIndex('doc_indx');
        $table->dropColumn('doc_type');
        $table->dropColumn('doc_id');

        $table = $schema->getTable('comiss');
        $table->dropIndex('doc_indx');
        $table->dropColumn('doc_type');
        $table->dropColumn('doc_id');

        $table = $schema->getTable('orders');
        $table->dropIndex('status_ex_indx');
        $table->dropIndex('status_account_indx');
        $table->dropColumn('status_ex');
        $table->dropColumn('status_account');

        $table = $schema->getTable('bid');
        $table->dropIndex('take_indx');
        $table->dropColumn('take');

        $table = $schema->getTable('vt');
        $table->dropIndex('status_account_indx');
        $table->dropColumn('status_account');

        $table = $schema->getTable('vt_good');
        $table->dropIndex('take_indx');
        $table->dropColumn('take');

        $table = $schema->getTable('st');
        $table->dropIndex('status_account_indx');
        $table->dropColumn('status_account');

        $table = $schema->getTable('st_good');
        $table->dropIndex('take_indx');
        $table->dropColumn('take');
        
        $table = $schema->getTable('pt');
        $table->dropIndex('status_account_indx');
        $table->dropColumn('status_account');

        $table = $schema->getTable('pt_good');
        $table->dropIndex('take_indx');
        $table->dropColumn('take');
    }
}
