<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Movement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402074050 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->addColumn('amount_extra', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Сумма дополнительная']);
        $table->addColumn('amount_extra_type', 'integer', ['notnull' => true, 'default' => Movement::EXTRA_AMOUNT_UNKNOWN, 'comment' => 'Тип суммы дополнительной']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->dropColumn('amount_extra');
        $table->dropColumn('amount_extra_type');
    }
}
