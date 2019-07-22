<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190722144457 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->addColumn('apl_id', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('status_ex', 'integer', ['notnull' => false, 'default' => \Application\Entity\Attribute::EX_TO_TRANSFER]);
        $table = $schema->getTable('attribute_value');
        $table->addColumn('apl_id', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('status_ex', 'integer', ['notnull' => false, 'default' => \Application\Entity\AttributeValue::EX_TO_TRANSFER]);
        $table = $schema->getTable('good_attribute_value');
        $table->addColumn('apl_id', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('status_ex', 'integer', ['notnull' => false, 'default' => \Application\Entity\GoodAttributeValue::EX_TO_TRANSFER]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->dropColumn('apl_id');
        $table->dropColumn('status_ex');
        $table = $schema->getTable('attribute_value');
        $table->dropColumn('apl_id');
        $table->dropColumn('status_ex');
        $table = $schema->getTable('good_attribute_value');
        $table->dropColumn('apl_id');
        $table->dropColumn('status_ex');
    }
}
