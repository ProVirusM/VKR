<?php

namespace App\Entity;

use App\Repository\ChatsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatsRepository::class)]
class Chats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'chats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $cst_id = null;

    #[ORM\ManyToOne(inversedBy: 'chats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contractors $cnt_id = null;



    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'chat_id', orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getChtId(): ?int
//    {
//        return $this->cht_id;
//    }

    public function setChtId(int $cht_id): static
    {
        $this->id = $cht_id;

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

    public function getCntId(): ?Contractors
    {
        return $this->cnt_id;
    }

    public function setCntId(?Contractors $cnt_id): static
    {
        $this->cnt_id = $cnt_id;

        return $this;
    }



    /**
     * @return Collection<int, Messages>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setChatId($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatId() === $this) {
                $message->setChatId(null);
            }
        }

        return $this;
    }
}
