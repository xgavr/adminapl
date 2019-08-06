<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190806084133 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->addColumn('similar_good', 'integer', ['notnull' => true, 'default' => \Application\Entity\Attribute::FOR_SIMILAR_NO_GOOD]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->dropColumn('similar_good');

    }
}
