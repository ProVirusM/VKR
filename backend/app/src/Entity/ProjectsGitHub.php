<?php

namespace App\Entity;

use App\Repository\ProjectsGitHubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectsGitHubRepository::class)]
class ProjectsGitHub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $pgh_id = null;

    #[ORM\Column(length: 255)]
    private ?string $pgh_name = null;

    #[ORM\Column(length: 255)]
    private ?string $pgh_repository = null;

    #[ORM\ManyToOne(inversedBy: 'projectsGitHubs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contractors $cnt_id = null;

    #[ORM\Column(length: 255)]
    private ?string $pgh_text = null;

    /**
     * @var Collection<int, PhotosProjectsGitHub>
     */
    #[ORM\OneToMany(targetEntity: PhotosProjectsGitHub::class, mappedBy: 'pgh_id', orphanRemoval: true)]
    private Collection $photosProjectsGitHubs;

    public function __construct()
    {
        $this->photosProjectsGitHubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getPghId(): ?int
//    {
//        return $this->pgh_id;
//    }

    public function setPghId(int $pgh_id): static
    {
        $this->id = $pgh_id;

        return $this;
    }

    public function getPghName(): ?string
    {
        return $this->pgh_name;
    }

    public function setPghName(string $pgh_name): static
    {
        $this->pgh_name = $pgh_name;

        return $this;
    }

    public function getPghRepository(): ?string
    {
        return $this->pgh_repository;
    }

    public function setPghRepository(string $pgh_repository): static
    {
        $this->pgh_repository = $pgh_repository;

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

    public function getPghText(): ?string
    {
        return $this->pgh_text;
    }

    public function setPghText(string $pgh_text): static
    {
        $this->pgh_text = $pgh_text;

        return $this;
    }

    /**
     * @return Collection<int, PhotosProjectsGitHub>
     */
    public function getPhotosProjectsGitHubs(): Collection
    {
        return $this->photosProjectsGitHubs;
    }

    public function addPhotosProjectsGitHub(PhotosProjectsGitHub $photosProjectsGitHub): static
    {
        if (!$this->photosProjectsGitHubs->contains($photosProjectsGitHub)) {
            $this->photosProjectsGitHubs->add($photosProjectsGitHub);
            $photosProjectsGitHub->setPghId($this);
        }

        return $this;
    }

    public function removePhotosProjectsGitHub(PhotosProjectsGitHub $photosProjectsGitHub): static
    {
        if ($this->photosProjectsGitHubs->removeElement($photosProjectsGitHub)) {
            // set the owning side to null (unless already changed)
            if ($photosProjectsGitHub->getPghId() === $this) {
                $photosProjectsGitHub->setPghId(null);
            }
        }

        return $this;
    }
}
