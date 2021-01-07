<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Admin\Entity\PostLog;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210106160243 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('post_log');
        $table->addColumn('act', 'integer', ['notnull'=>true, 'default' => PostLog::ACT_NO]);
        $table->addIndex(['status'], 'status_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('post_log');
        $table->dropIndex('status_indx');
        $table->dropColumn('act');        
    }
}
