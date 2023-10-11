<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011072140 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->addColumn('balance', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Текщий баланс']);
        
        $table = $schema->getTable('user_transaction');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Метка документа']);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');

        $table = $schema->getTable('cash_transaction');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Метка документа']);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');

        $table = $schema->getTable('register');
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64, 'default'=>'', 'comment' => 'Код документа']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->dropColumn('balance');

        $table = $schema->getTable('user_transaction');
        $table->dropColumn('doc_stamp');

        $table = $schema->getTable('cash_transaction');
        $table->dropColumn('doc_stamp');

        $table = $schema->getTable('register');
        $table->dropColumn('doc_key');
    }
}
