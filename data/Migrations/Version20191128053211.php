<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Goods;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128053211 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('status_name_ex', 'integer', ['notnull' => true, 'default' => Goods::NAME_EX_NEW]);
        $table->addIndex(['status_name_ex'], 'status_name_ex');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropIndex('status_name_ex');
        $table->dropColumn('status_name_ex');
    }
}
