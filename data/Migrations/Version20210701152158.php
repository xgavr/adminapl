<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701152158 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('mutual');
        $table->addColumn('contact_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_mutual_contact_id_fk');

        $table = $schema->getTable('bid');
        $table->addColumn('row_no', 'integer', ['notnull' => true, 'default' => 0]);
}

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('mutual');
        $table->removeForeignKey('contact_id_mutual_contact_id_fk');
        $table->dropColumn('contact_id');

        $table = $schema->getTable('bid');
        $table->dropColumn('row_no');
    }
}
