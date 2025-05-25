<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525130208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chats DROP CONSTRAINT fk_2d68180fb0e177a8');
        $this->addSql('DROP INDEX idx_2d68180fb0e177a8');
        $this->addSql('ALTER TABLE chats DROP ord_id_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE chats ADD ord_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT fk_2d68180fb0e177a8 FOREIGN KEY (ord_id_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2d68180fb0e177a8 ON chats (ord_id_id)');
    }
}
