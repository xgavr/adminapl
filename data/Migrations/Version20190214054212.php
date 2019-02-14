<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190214054212 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('post_log');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('to', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('from', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('body', 'text', ['notnull' => false]);
        $table->addColumn('attachment', 'text', ['notnull' => false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>false]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('post_log');
    }
}
