<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $msg_id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chats $chat_id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contractors $cnt_id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $cst_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $msg_timestamp = null;

    #[ORM\Column(length: 255)]
    private ?string $msg_text = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getMsgId(): ?int
//    {
//        return $this->msg_id;
//    }

    public function setMsgId(int $msg_id): static
    {
        $this->id = $msg_id;

        return $this;
    }

    public function getChatId(): ?Chats
    {
        return $this->chat_id;
    }

    public function setChatId(?Chats $chat_id): static
    {
        $this->chat_id = $chat_id;

        return $this;
    }

    public function getCntId(): ?Contractors
    {
        return $this->cnt_id;
    }

    public function setCntId(?Contractors $cnt_id): static
    {
        $this->cnt_id = $cnt_id;

        return $this;
    }

    public function getCstId(): ?Customers
    {
        return $this->cst_id;
    }

    public function setCstId(?Customers $cst_id): static
    {
        $this->cst_id = $cst_id;

        return $this;
    }

    public function getMsgTimestamp(): ?\DateTimeInterface
    {
        return $this->msg_timestamp;
    }

    public function setMsgTimestamp(\DateTimeInterface $msg_timestamp): static
    {
        $this->msg_timestamp = $msg_timestamp;

        return $this;
    }

    public function getMsgText(): ?string
    {
        return $this->msg_text;
    }

    public function setMsgText(string $msg_text): static
    {
        $this->msg_text = $msg_text;

        return $this;
    }
}
