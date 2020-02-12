<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200212152225 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_token');
        $table->dropIndex('article_id_lemma_indx');
        $table->addUniqueIndex(['article_id', 'title_id', 'lemma'], 'article_title_lemma_uindx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_token');
        $table->dropIndex('article_title_lemma_uindx');
        $table->addUniqueIndex(['article_id', 'lemma'], 'article_id_lemma_indx');
    }
}
