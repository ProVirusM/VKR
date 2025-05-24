<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524195547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_495867ECA36131C7 ON directions (drc_title)');
        $this->addSql('CREATE INDEX drc_title_idx ON directions (drc_title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A0D15379C7E293A8 ON languages (lng_title)');
        $this->addSql('CREATE INDEX lng_title_idx ON languages (lng_title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B628EF36ADAF17F8 ON stacks (stc_title)');
        $this->addSql('CREATE INDEX stc_title_idx ON stacks (stc_title)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_495867ECA36131C7');
        $this->addSql('DROP INDEX drc_title_idx');
        $this->addSql('DROP INDEX UNIQ_A0D15379C7E293A8');
        $this->addSql('DROP INDEX lng_title_idx');
        $this->addSql('DROP INDEX UNIQ_B628EF36ADAF17F8');
        $this->addSql('DROP INDEX stc_title_idx');
    }
}
