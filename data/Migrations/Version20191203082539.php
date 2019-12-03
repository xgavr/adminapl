<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Attribute;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203082539 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->addColumn('to_name', 'integer', ['notnull' => true, 'default' => Attribute::NO_BEST_NAME]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('attribute');
        $table->dropColumn('to_name');
    }
}
