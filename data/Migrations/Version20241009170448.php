<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009170448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fold_balance');
        $table->addColumn('fold_code', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Код']);
        $table->addColumn('fold_name', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Наименование']);

        $table = $schema->getTable('rack');
        $table->addUniqueIndex(['code'], 'code_uindx');

        $table = $schema->getTable('shelf');
        $table->addUniqueIndex(['code'], 'code_uindx');

        $table = $schema->getTable('cell');
        $table->addUniqueIndex(['code'], 'code_uindx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rack');
        $table->dropIndex('code_uindx');

        $table = $schema->getTable('shelf');
        $table->dropIndex('code_uindx');

        $table = $schema->getTable('cell');
        $table->dropIndex('code_uindx');

        $table = $schema->getTable('fold_balance');
        $table->dropColumn('fold_code');
        $table->dropColumn('fold_name');
    }
}
