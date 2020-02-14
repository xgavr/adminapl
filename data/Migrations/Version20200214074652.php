<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214074652 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_title');
        $table->addColumn('token_group_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('token_group_title', 'text', ['notnull' => false]);
        $table->addColumn('token_group_title_md5', 'string', ['notnull' => true, 'default' => '', 'length' => '128']);
        $table->addIndex(['token_group_id'], 'token_group_indx');
        $table->addIndex(['token_group_title_md5'], 'token_group_title_md5_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article-title');
        $table->dropColumn('token_group_id');
        $table->dropColumn('token_group_title');
        $table->dropColumn('token_group_title_md5');
    }
}
