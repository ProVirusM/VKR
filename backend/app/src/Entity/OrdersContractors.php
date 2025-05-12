<?php

namespace App\Entity;

use App\Repository\OrdersContractorsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersContractorsRepository::class)]
class OrdersContractors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $ord_cnt_id = null;

    #[ORM\ManyToOne(inversedBy: 'ordersContractors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $ord_id = null;

    #[ORM\ManyToOne(inversedBy: 'ordersContractors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contractors $cnt_id = null;

    #[ORM\Column(length: 255)]
    private ?string $ord_cnt_status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getOrdCntId(): ?int
//    {
//        return $this->ord_cnt_id;
//    }

    public function setOrdCntId(int $ord_cnt_id): static
    {
        $this->id = $ord_cnt_id;

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

    public function getCntId(): ?Contractors
    {
        return $this->cnt_id;
    }

    public function setCntId(?Contractors $cnt_id): static
    {
        $this->cnt_id = $cnt_id;

        return $this;
    }

    public function getOrdCntStatus(): ?string
    {
        return $this->ord_cnt_status;
    }

    public function setOrdCntStatus(string $ord_cnt_status): static
    {
        $this->ord_cnt_status = $ord_cnt_status;

        return $this;
    }
}
