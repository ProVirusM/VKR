<?php

namespace App\Entity;

use App\Repository\StacksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StacksRepository::class)]
class Stacks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $stc_id = null;

    #[ORM\Column(length: 255)]
    private ?string $stc_title = null;



    #[ORM\ManyToOne(inversedBy: 'stacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Directions $drc_id = null;

    /**
     * @var Collection<int, OrdersStacks>
     */
    #[ORM\OneToMany(targetEntity: OrdersStacks::class, mappedBy: 'stc_id')]
    private Collection $ordersStacks;

    #[ORM\ManyToOne(inversedBy: 'stacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Languages $lng_id = null;

    public function __construct()
    {
        $this->ordersStacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getStcId(): ?int
//    {
//        return $this->stc_id;
//    }

    public function setStcId(int $stc_id): static
    {
        $this->id = $stc_id;

        return $this;
    }

    public function getStcTitle(): ?string
    {
        return $this->stc_title;
    }

    public function setStcTitle(string $stc_title): static
    {
        $this->stc_title = $stc_title;

        return $this;
    }



    public function getDrcId(): ?Directions
    {
        return $this->drc_id;
    }

    public function setDrcId(?Directions $drc_id): static
    {
        $this->drc_id = $drc_id;

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
            $ordersStack->setStcId($this);
        }

        return $this;
    }

    public function removeOrdersStack(OrdersStacks $ordersStack): static
    {
        if ($this->ordersStacks->removeElement($ordersStack)) {
            // set the owning side to null (unless already changed)
            if ($ordersStack->getStcId() === $this) {
                $ordersStack->setStcId(null);
            }
        }

        return $this;
    }

    public function getLngId(): ?Languages
    {
        return $this->lng_id;
    }

    public function setLngId(?Languages $lng_id): static
    {
        $this->lng_id = $lng_id;

        return $this;
    }
}
