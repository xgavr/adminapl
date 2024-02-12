<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240212112908 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->addColumn('esn', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'ЕСН']);        
        $table->addColumn('profit_net', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Чистая прибыль']);        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->dropColumn('esn');
        $table->dropColumn('profit_net');
    }
}
