<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180215085840 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contact');
        $table->addColumn('icq', 'integer', ['notnull'=>false]);
        $table->addColumn('telegramm', 'integer', ['notnull'=>false]);

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contact');
        $table->dropColumn('icq');
        $table->dropColumn('telegramm');

    }
}
