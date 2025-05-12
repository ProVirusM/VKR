<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512113933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chats (id SERIAL NOT NULL, cst_id_id INT NOT NULL, cnt_id_id INT NOT NULL, ord_id_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D68180FFDA118EC ON chats (cst_id_id)');
        $this->addSql('CREATE INDEX IDX_2D68180F1A1F3C16 ON chats (cnt_id_id)');
        $this->addSql('CREATE INDEX IDX_2D68180FB0E177A8 ON chats (ord_id_id)');
        $this->addSql('CREATE TABLE contractors (id SERIAL NOT NULL, usr_id_id INT NOT NULL, cnt_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2BF84B3041AB162D ON contractors (usr_id_id)');
        $this->addSql('CREATE TABLE customers (id SERIAL NOT NULL, usr_id_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62534E2141AB162D ON customers (usr_id_id)');
        $this->addSql('CREATE TABLE directions (id SERIAL NOT NULL, drc_title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE feedbacks (id SERIAL NOT NULL, cnt_id_id INT NOT NULL, cst_id_id INT NOT NULL, fdb_text VARCHAR(255) NOT NULL, fdb_estimation INT NOT NULL, fdb_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7E6C3F891A1F3C16 ON feedbacks (cnt_id_id)');
        $this->addSql('CREATE INDEX IDX_7E6C3F89FDA118EC ON feedbacks (cst_id_id)');
        $this->addSql('CREATE TABLE languages (id SERIAL NOT NULL, lng_title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messages (id SERIAL NOT NULL, chat_id_id INT NOT NULL, cnt_id_id INT NOT NULL, cst_id_id INT NOT NULL, msg_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, msg_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB021E967E3973CC ON messages (chat_id_id)');
        $this->addSql('CREATE INDEX IDX_DB021E961A1F3C16 ON messages (cnt_id_id)');
        $this->addSql('CREATE INDEX IDX_DB021E96FDA118EC ON messages (cst_id_id)');
        $this->addSql('CREATE TABLE orders (id SERIAL NOT NULL, cst_id_id INT NOT NULL, ord_title VARCHAR(255) NOT NULL, ord_text VARCHAR(255) NOT NULL, ord_status VARCHAR(255) NOT NULL, ord_price INT NOT NULL, ord_time VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E52FFDEEFDA118EC ON orders (cst_id_id)');
        $this->addSql('CREATE TABLE orders_contractors (id SERIAL NOT NULL, ord_id_id INT NOT NULL, cnt_id_id INT NOT NULL, ord_cnt_status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_58419FAAB0E177A8 ON orders_contractors (ord_id_id)');
        $this->addSql('CREATE INDEX IDX_58419FAA1A1F3C16 ON orders_contractors (cnt_id_id)');
        $this->addSql('CREATE TABLE orders_stacks (id SERIAL NOT NULL, ord_id_id INT NOT NULL, stc_id_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_17B8D6E7B0E177A8 ON orders_stacks (ord_id_id)');
        $this->addSql('CREATE INDEX IDX_17B8D6E7F9DDF1C4 ON orders_stacks (stc_id_id)');
        $this->addSql('CREATE TABLE photos_projects_git_hub (id SERIAL NOT NULL, pgh_id_id INT NOT NULL, ppgh_link VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B3E610213063AF33 ON photos_projects_git_hub (pgh_id_id)');
        $this->addSql('CREATE TABLE projects_git_hub (id SERIAL NOT NULL, cnt_id_id INT NOT NULL, pgh_name VARCHAR(255) NOT NULL, pgh_repository VARCHAR(255) NOT NULL, pgh_text VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F949A6321A1F3C16 ON projects_git_hub (cnt_id_id)');
        $this->addSql('CREATE TABLE stacks (id SERIAL NOT NULL, drc_id_id INT NOT NULL, lng_id_id INT NOT NULL, stc_title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B628EF36F713D7FB ON stacks (drc_id_id)');
        $this->addSql('CREATE INDEX IDX_B628EF3693907594 ON stacks (lng_id_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, usr_name VARCHAR(255) NOT NULL, usr_surname VARCHAR(255) NOT NULL, usr_patronymic VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT FK_2D68180FFDA118EC FOREIGN KEY (cst_id_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT FK_2D68180F1A1F3C16 FOREIGN KEY (cnt_id_id) REFERENCES contractors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chats ADD CONSTRAINT FK_2D68180FB0E177A8 FOREIGN KEY (ord_id_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contractors ADD CONSTRAINT FK_2BF84B3041AB162D FOREIGN KEY (usr_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E2141AB162D FOREIGN KEY (usr_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedbacks ADD CONSTRAINT FK_7E6C3F891A1F3C16 FOREIGN KEY (cnt_id_id) REFERENCES contractors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedbacks ADD CONSTRAINT FK_7E6C3F89FDA118EC FOREIGN KEY (cst_id_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E967E3973CC FOREIGN KEY (chat_id_id) REFERENCES chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E961A1F3C16 FOREIGN KEY (cnt_id_id) REFERENCES contractors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96FDA118EC FOREIGN KEY (cst_id_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEFDA118EC FOREIGN KEY (cst_id_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_contractors ADD CONSTRAINT FK_58419FAAB0E177A8 FOREIGN KEY (ord_id_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_contractors ADD CONSTRAINT FK_58419FAA1A1F3C16 FOREIGN KEY (cnt_id_id) REFERENCES contractors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_stacks ADD CONSTRAINT FK_17B8D6E7B0E177A8 FOREIGN KEY (ord_id_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_stacks ADD CONSTRAINT FK_17B8D6E7F9DDF1C4 FOREIGN KEY (stc_id_id) REFERENCES stacks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE photos_projects_git_hub ADD CONSTRAINT FK_B3E610213063AF33 FOREIGN KEY (pgh_id_id) REFERENCES projects_git_hub (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE projects_git_hub ADD CONSTRAINT FK_F949A6321A1F3C16 FOREIGN KEY (cnt_id_id) REFERENCES contractors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stacks ADD CONSTRAINT FK_B628EF36F713D7FB FOREIGN KEY (drc_id_id) REFERENCES directions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stacks ADD CONSTRAINT FK_B628EF3693907594 FOREIGN KEY (lng_id_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE chats DROP CONSTRAINT FK_2D68180FFDA118EC');
        $this->addSql('ALTER TABLE chats DROP CONSTRAINT FK_2D68180F1A1F3C16');
        $this->addSql('ALTER TABLE chats DROP CONSTRAINT FK_2D68180FB0E177A8');
        $this->addSql('ALTER TABLE contractors DROP CONSTRAINT FK_2BF84B3041AB162D');
        $this->addSql('ALTER TABLE customers DROP CONSTRAINT FK_62534E2141AB162D');
        $this->addSql('ALTER TABLE feedbacks DROP CONSTRAINT FK_7E6C3F891A1F3C16');
        $this->addSql('ALTER TABLE feedbacks DROP CONSTRAINT FK_7E6C3F89FDA118EC');
        $this->addSql('ALTER TABLE messages DROP CONSTRAINT FK_DB021E967E3973CC');
        $this->addSql('ALTER TABLE messages DROP CONSTRAINT FK_DB021E961A1F3C16');
        $this->addSql('ALTER TABLE messages DROP CONSTRAINT FK_DB021E96FDA118EC');
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEEFDA118EC');
        $this->addSql('ALTER TABLE orders_contractors DROP CONSTRAINT FK_58419FAAB0E177A8');
        $this->addSql('ALTER TABLE orders_contractors DROP CONSTRAINT FK_58419FAA1A1F3C16');
        $this->addSql('ALTER TABLE orders_stacks DROP CONSTRAINT FK_17B8D6E7B0E177A8');
        $this->addSql('ALTER TABLE orders_stacks DROP CONSTRAINT FK_17B8D6E7F9DDF1C4');
        $this->addSql('ALTER TABLE photos_projects_git_hub DROP CONSTRAINT FK_B3E610213063AF33');
        $this->addSql('ALTER TABLE projects_git_hub DROP CONSTRAINT FK_F949A6321A1F3C16');
        $this->addSql('ALTER TABLE stacks DROP CONSTRAINT FK_B628EF36F713D7FB');
        $this->addSql('ALTER TABLE stacks DROP CONSTRAINT FK_B628EF3693907594');
        $this->addSql('DROP TABLE chats');
        $this->addSql('DROP TABLE contractors');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE directions');
        $this->addSql('DROP TABLE feedbacks');
        $this->addSql('DROP TABLE languages');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE orders_contractors');
        $this->addSql('DROP TABLE orders_stacks');
        $this->addSql('DROP TABLE photos_projects_git_hub');
        $this->addSql('DROP TABLE projects_git_hub');
        $this->addSql('DROP TABLE stacks');
        $this->addSql('DROP TABLE "user"');
    }
}
