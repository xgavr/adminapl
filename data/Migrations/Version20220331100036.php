<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220331100036 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->addColumn('doc_num_col', 'integer', ['notnull' => false]);
        $table->addColumn('doc_num_row', 'integer', ['notnull' => false]);
        $table->addColumn('doc_date_col', 'integer', ['notnull' => false]);
        $table->addColumn('doc_date_row', 'integer', ['notnull' => false]);
        $table->addColumn('cor_num_col', 'integer', ['notnull' => false]);
        $table->addColumn('cor_num_row', 'integer', ['notnull' => false]);
        $table->addColumn('cor_date_col', 'integer', ['notnull' => false]);
        $table->addColumn('cor_date_row', 'integer', ['notnull' => false]);
        $table->addColumn('id_num_col', 'integer', ['notnull' => false]);
        $table->addColumn('id_num_row', 'integer', ['notnull' => false]);
        $table->addColumn('id_date_col', 'integer', ['notnull' => false]);
        $table->addColumn('id_date_row', 'integer', ['notnull' => false]);
        $table->addColumn('contract_col', 'integer', ['notnull' => false]);
        $table->addColumn('contract_row', 'integer', ['notnull' => false]);
        $table->addColumn('tag_non_cash_col', 'integer', ['notnull' => false]);
        $table->addColumn('tag_non_cash_row', 'integer', ['notnull' => false]);
        $table->addColumn('tag_non_cash_value', 'integer', ['notnull' => false]);
        $table->addColumn('init_tab_row', 'integer', ['notnull' => false]);
        $table->addColumn('article_col', 'integer', ['notnull' => false]);
        $table->addColumn('supplier_id_col', 'integer', ['notnull' => false]);
        $table->addColumn('good_name_col', 'integer', ['notnull' => false]);
        $table->addColumn('producer_col', 'integer', ['notnull' => false]);
        $table->addColumn('quantity_col', 'integer', ['notnull' => false]);
        $table->addColumn('price_col', 'integer', ['notnull' => false]);
        $table->addColumn('amount_col', 'integer', ['notnull' => false]);
        $table->addColumn('package_code_col', 'integer', ['notnull' => false]);
        $table->addColumn('package_col', 'integer', ['notnull' => false]);
        $table->addColumn('country_code_col', 'integer', ['notnull' => false]);
        $table->addColumn('country_col', 'integer', ['notnull' => false]);
        $table->addColumn('ntd_col', 'integer', ['notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->dropColumn('doc_num_col');
        $table->dropColumn('doc_num_row');
        $table->dropColumn('doc_date_col');
        $table->dropColumn('doc_date_row');
        $table->dropColumn('cor_num_col');
        $table->dropColumn('cor_num_row');
        $table->dropColumn('cor_date_col');
        $table->dropColumn('cor_date_row');
        $table->dropColumn('id_num_col');
        $table->dropColumn('id_date_row');
        $table->dropColumn('id_num_row');
        $table->dropColumn('id_date_col');
        $table->dropColumn('id_date_row');
        $table->dropColumn('contract_col');
        $table->dropColumn('contract_row');
        $table->dropColumn('tag_non_cash_col');
        $table->dropColumn('tag_non_cash_row');
        $table->dropColumn('tag_non_cash_value');
        $table->dropColumn('init_tab_row');
        $table->dropColumn('article_col');
        $table->dropColumn('supplier_id_col');
        $table->dropColumn('good_name_col');
        $table->dropColumn('producer_col');
        $table->dropColumn('quantity_col');
        $table->dropColumn('price_col');
        $table->dropColumn('amount_col');
        $table->dropColumn('package_code_col');
        $table->dropColumn('package_col');
        $table->dropColumn('country_code_col');
        $table->dropColumn('country_col');
        $table->dropColumn('ntd_col');
    }
}
