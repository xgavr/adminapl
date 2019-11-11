<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Goods;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191111145915 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('td_direct', 'integer', ['notnull' => true, 'default' => Goods::TD_NO_DIRECT]);
        $table->addColumn('group_token_update_flag', 'integer', ['notnull' => true, 'default' => 10]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('td_direct');
        $table->dropColumn('group_token_update_flag');
    }
}
