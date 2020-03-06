<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200306072227 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_token');
        $table->addColumn('token_group_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status_take', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addIndex(['token_group_id'], 'token_group_indx');
        $table->addIndex(['status_take'], 'status_take_indx');

        $table = $schema->getTable('article_bigram');
        $table->addColumn('token_group_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status_take', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addIndex(['token_group_id'], 'token_group_indx');
        $table->addIndex(['status_take'], 'status_take_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_token');
        $table->dropColumn('token_group_id');
        $table->dropColumn('status_take');

        $table = $schema->getTable('article_bigram');
        $table->dropColumn('token_group_id');
        $table->dropColumn('status_take');
    }
}
