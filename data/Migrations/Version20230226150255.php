<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230226150255 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_gettings');
        $table->addColumn('app_password', 'string', ['notnull' => false, 'length' => 128]);        

        $table = $schema->getTable('bill_gettings');
        $table->addColumn('app_password', 'string', ['notnull' => false, 'length' => 128]);        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('price_gettings');
        $table->dropColumn('app_password');

        $table = $schema->getTable('bill_gettings');
        $table->dropColumn('app_password');
    }
}
