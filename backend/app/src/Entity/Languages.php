<?php

namespace App\Entity;

use App\Repository\LanguagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguagesRepository::class)]
class Languages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $lng_id = null;

    #[ORM\Column(length: 255)]
    private ?string $lng_title = null;

    /**
     * @var Collection<int, Stacks>
     */
    #[ORM\OneToMany(targetEntity: Stacks::class, mappedBy: 'lng_id', orphanRemoval: true)]
    private Collection $stacks;

    public function __construct()
    {
        $this->stacks = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getLngId(): ?int
//    {
//        return $this->lng_id;
//    }

    public function setLngId(int $lng_id): static
    {
        $this->id = $lng_id;

        return $this;
    }

    public function getLngTitle(): ?string
    {
        return $this->lng_title;
    }

    public function setLngTitle(string $lng_title): static
    {
        $this->lng_title = $lng_title;

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
            $stack->setLngId($this);
        }

        return $this;
    }

    public function removeStack(Stacks $stack): static
    {
        if ($this->stacks->removeElement($stack)) {
            // set the owning side to null (unless already changed)
            if ($stack->getLngId() === $this) {
                $stack->setLngId(null);
            }
        }

        return $this;
    }


}
