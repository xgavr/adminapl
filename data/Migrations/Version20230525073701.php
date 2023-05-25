<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Comment;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525073701 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('wammchat');
        $table->addColumn('comment_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('comment', ['comment_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'comment_id_wammchat_comment_id_fk');
        
        $table = $schema->getTable('comment');
        $table->addColumn('status_ex', 'integer', ['notnull' => true, 'default' => Comment::STATUS_EX_RECD]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('wammchat');
        $table->removeForeignKey('comment_id_wammchat_comment_id_fk');
        $table->dropColumn('comment_id');
        
        $table = $schema->getTable('comment');
        $table->dropColumn('status_ex');
    }
    
}
