<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $ord_id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $ord_title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $ord_text = null;



    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $ord_status = null;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $ord_price = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $ord_time = null;

    /**
     * @var Collection<int, OrdersContractors>
     */
    #[ORM\OneToMany(targetEntity: OrdersContractors::class, mappedBy: 'ord_id', orphanRemoval: true)]
    private Collection $ordersContractors;

    /**
     * @var Collection<int, OrdersStacks>
     */
    #[ORM\OneToMany(targetEntity: OrdersStacks::class, mappedBy: 'ord_id', cascade: ['persist'], orphanRemoval: true)]
    private Collection $ordersStacks;

    /**
     * @var Collection<int, Chats>
     */
    #[ORM\OneToMany(targetEntity: Chats::class, mappedBy: 'ord_id', orphanRemoval: true)]
    private Collection $chats;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $cst_id = null;

    public function __construct()
    {
        $this->ordersContractors = new ArrayCollection();
        $this->ordersStacks = new ArrayCollection();
        $this->chats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getOrdId(): ?int
//    {
//        return $this->ord_id;
//    }

    public function setOrdId(int $ord_id): static
    {
        $this->id = $ord_id;

        return $this;
    }

    public function getOrdTitle(): ?string
    {
        return $this->ord_title;
    }

    public function setOrdTitle(string $ord_title): static
    {
        $this->ord_title = $ord_title;

        return $this;
    }

    public function getOrdText(): ?string
    {
        return $this->ord_text;
    }

    public function setOrdText(string $ord_text): static
    {
        $this->ord_text = $ord_text;

        return $this;
    }



    public function getOrdStatus(): ?string
    {
        return $this->ord_status;
    }

    public function setOrdStatus(string $ord_status): static
    {
        $this->ord_status = $ord_status;

        return $this;
    }

    public function getOrdPrice(): ?int
    {
        return $this->ord_price;
    }

    public function setOrdPrice(int $ord_price): static
    {
        $this->ord_price = $ord_price;

        return $this;
    }

    public function getOrdTime(): ?string
    {
        return $this->ord_time;
    }

    public function setOrdTime(string $ord_time): static
    {
        $this->ord_time = $ord_time;

        return $this;
    }

    /**
     * @return Collection<int, OrdersContractors>
     */
    public function getOrdersContractors(): Collection
    {
        return $this->ordersContractors;
    }

    public function addOrdersContractor(OrdersContractors $ordersContractor): static
    {
        if (!$this->ordersContractors->contains($ordersContractor)) {
            $this->ordersContractors->add($ordersContractor);
            $ordersContractor->setOrdId($this);
        }

        return $this;
    }

    public function removeOrdersContractor(OrdersContractors $ordersContractor): static
    {
        if ($this->ordersContractors->removeElement($ordersContractor)) {
            // set the owning side to null (unless already changed)
            if ($ordersContractor->getOrdId() === $this) {
                $ordersContractor->setOrdId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrdersStacks>
     */
    public function getOrdersStacks(): Collection
    {
        return $this->ordersStacks;
    }

    public function addOrdersStack(OrdersStacks $ordersStack): static
    {
        if (!$this->ordersStacks->contains($ordersStack)) {
            $this->ordersStacks->add($ordersStack);
            $ordersStack->setOrdId($this);
        }

        return $this;
    }

    public function removeOrdersStack(OrdersStacks $ordersStack): static
    {
        if ($this->ordersStacks->removeElement($ordersStack)) {
            // set the owning side to null (unless already changed)
            if ($ordersStack->getOrdId() === $this) {
                $ordersStack->setOrdId(null);
            }
        }

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
            $chat->setOrdId($this);
        }

        return $this;
    }

    public function removeChat(Chats $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getOrdId() === $this) {
                $chat->setOrdId(null);
            }
        }

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
}
