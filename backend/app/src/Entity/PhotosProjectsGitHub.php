<?php

namespace App\Entity;

use App\Repository\PhotosProjectsGitHubRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotosProjectsGitHubRepository::class)]
class PhotosProjectsGitHub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $ppgh_id = null;

    #[ORM\Column(length: 255)]
    private ?string $ppgh_link = null;

    #[ORM\ManyToOne(inversedBy: 'photosProjectsGitHubs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectsGitHub $pgh_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getPpghId(): ?int
//    {
//        return $this->ppgh_id;
//    }

    public function setPpghId(int $ppgh_id): static
    {
        $this->id = $ppgh_id;

        return $this;
    }

    public function getPpghLink(): ?string
    {
        return $this->ppgh_link;
    }

    public function setPpghLink(string $ppgh_link): static
    {
        $this->ppgh_link = $ppgh_link;

        return $this;
    }

    public function getPghId(): ?ProjectsGitHub
    {
        return $this->pgh_id;
    }

    public function setPghId(?ProjectsGitHub $pgh_id): static
    {
        $this->pgh_id = $pgh_id;

        return $this;
    }
}
