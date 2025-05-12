<?php

namespace App\Entity;

use App\Repository\FeedbacksRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbacksRepository::class)]
class Feedbacks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $fdb_id = null;



    #[ORM\Column(length: 255)]
    private ?string $fdb_text = null;

    #[ORM\Column]
    private ?int $fdb_estimation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fdb_timestamp = null;

    #[ORM\ManyToOne(inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contractors $cnt_id = null;

    #[ORM\ManyToOne(inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $cst_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getFdbId(): ?int
//    {
//        return $this->fdb_id;
//    }

    public function setFdbId(int $fdb_id): static
    {
        $this->id = $fdb_id;

        return $this;
    }

    public function getCntId(): ?int
    {
        return $this->cnt_id;
    }

    public function setCntId(int $cnt_id): static
    {
        $this->cnt_id = $cnt_id;

        return $this;
    }

    public function getCstId(): ?int
    {
        return $this->cst_id;
    }

    public function setCstId(int $cst_id): static
    {
        $this->cst_id = $cst_id;

        return $this;
    }

    public function getFdbText(): ?string
    {
        return $this->fdb_text;
    }

    public function setFdbText(string $fdb_text): static
    {
        $this->fdb_text = $fdb_text;

        return $this;
    }

    public function getFdbEstimation(): ?int
    {
        return $this->fdb_estimation;
    }

    public function setFdbEstimation(int $fdb_estimation): static
    {
        $this->fdb_estimation = $fdb_estimation;

        return $this;
    }

    public function getFdbTimestamp(): ?\DateTimeInterface
    {
        return $this->fdb_timestamp;
    }

    public function setFdbTimestamp(\DateTimeInterface $fdb_timestamp): static
    {
        $this->fdb_timestamp = $fdb_timestamp;

        return $this;
    }
}
