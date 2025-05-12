<?php

namespace App\Entity;

use App\Repository\OrdersStacksRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersStacksRepository::class)]
class OrdersStacks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $ord_stc_id = null;

    #[ORM\ManyToOne(inversedBy: 'ordersStacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $ord_id = null;

    #[ORM\ManyToOne(inversedBy: 'ordersStacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stacks $stc_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getOrdStcId(): ?int
//    {
//        return $this->ord_stc_id;
//    }

    public function setOrdStcId(int $ord_stc_id): static
    {
        $this->id = $ord_stc_id;

        return $this;
    }

    public function getOrdId(): ?Orders
    {
        return $this->ord_id;
    }

    public function setOrdId(?Orders $ord_id): static
    {
        $this->ord_id = $ord_id;

        return $this;
    }

    public function getStcId(): ?Stacks
    {
        return $this->stc_id;
    }

    public function setStcId(?Stacks $stc_id): static
    {
        $this->stc_id = $stc_id;

        return $this;
    }
}
