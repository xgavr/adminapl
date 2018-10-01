<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181001073711 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->changeColumn('oem', ['type' => Type::getType('text'), 'length' => 65535]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->changeColumn('oem', ['type' => Type::getType('string'), 'length' => 32]);

    }
}
