<?php

namespace App\Entity;

use App\Repository\CustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomersRepository::class)]
class Customers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $cst_id = null;




    /**
     * @var Collection<int, Chats>
     */
    #[ORM\OneToMany(targetEntity: Chats::class, mappedBy: 'cst_id')]
    private Collection $chats;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'cst_id', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\OneToOne(inversedBy: 'customers', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usr_id = null;

    /**
     * @var Collection<int, Feedbacks>
     */
    #[ORM\OneToMany(targetEntity: Feedbacks::class, mappedBy: 'cst_id')]
    private Collection $feedbacks;

    /**
     * @var Collection<int, Orders>
     */
    #[ORM\OneToMany(targetEntity: Orders::class, mappedBy: 'cst_id')]
    private Collection $orders;

    public function __construct()
    {

        $this->chats = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getCstId(): ?int
//    {
//        return $this->cst_id;
//    }

    public function setCstId(int $cst_id): static
    {
        $this->id = $cst_id;

        return $this;
    }





    /**
     * @return Collection<int, Chats>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chats $chat): static
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
            $chat->setCstId($this);
        }

        return $this;
    }

    public function removeChat(Chats $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getCstId() === $this) {
                $chat->setCstId(null);
            }
        }

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
            $message->setCstId($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getCstId() === $this) {
                $message->setCstId(null);
            }
        }

        return $this;
    }

    public function getUsrId(): ?User
    {
        return $this->usr_id;
    }

    public function setUsrId(User $usr_id): static
    {
        $this->usr_id = $usr_id;

        return $this;
    }

    /**
     * @return Collection<int, Feedbacks>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedbacks $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setCstId($this);
        }

        return $this;
    }

    public function removeFeedback(Feedbacks $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getCstId() === $this) {
                $feedback->setCstId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Orders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCstId($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCstId() === $this) {
                $order->setCstId(null);
            }
        }

        return $this;
    }
}
