<?php

namespace App\Entity;

use App\Repository\DirectionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DirectionsRepository::class)]
#[ORM\Index(columns: ["drc_title"], name: "drc_title_idx", options: ["unique" => true])]
class Directions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $drc_id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $drc_title = null;

    /**
     * @var Collection<int, Stacks>
     */
    #[ORM\OneToMany(targetEntity: Stacks::class, mappedBy: 'drc_id', orphanRemoval: true)]
    private Collection $stacks;

    public function __construct()
    {
        $this->stacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getDrcId(): ?int
//    {
//        return $this->drc_id;
//    }

    public function setDrcId(int $drc_id): static
    {
        $this->id = $drc_id;

        return $this;
    }

    public function getDrcTitle(): ?string
    {
        return $this->drc_title;
    }

    public function setDrcTitle(string $drc_title): static
    {
        $this->drc_title = $drc_title;

        return $this;
    }

    /**
     * @return Collection<int, Stacks>
     */
    public function getStacks(): Collection
    {
        return $this->stacks;
    }

    public function addStack(Stacks $stack): static
    {
        if (!$this->stacks->contains($stack)) {
            $this->stacks->add($stack);
            $stack->setDrcId($this);
        }

        return $this;
    }

    public function removeStack(Stacks $stack): static
    {
        if ($this->stacks->removeElement($stack)) {
            // set the owning side to null (unless already changed)
            if ($stack->getDrcId() === $this) {
                $stack->setDrcId(null);
            }
        }

        return $this;
    }
}
