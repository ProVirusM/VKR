<?php

namespace App\Entity;

use App\Repository\ContractorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractorsRepository::class)]
class Contractors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\Column]
//    private ?int $cnt_id = null;

    #[ORM\Column(length: 255)]
    private ?string $cnt_text = null;


    /**
     * @var Collection<int, OrdersContractors>
     */
    #[ORM\OneToMany(targetEntity: OrdersContractors::class, mappedBy: 'cnt_id', orphanRemoval: true)]
    private Collection $ordersContractors;

    /**
     * @var Collection<int, ProjectsGitHub>
     */
    #[ORM\OneToMany(targetEntity: ProjectsGitHub::class, mappedBy: 'cnt_id', orphanRemoval: true)]
    private Collection $projectsGitHubs;

    /**
     * @var Collection<int, Chats>
     */
    #[ORM\OneToMany(targetEntity: Chats::class, mappedBy: 'cnt_id', orphanRemoval: true)]
    private Collection $chats;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'cnt_id', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\OneToOne(inversedBy: 'contractors', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usr_id = null;

    /**
     * @var Collection<int, Feedbacks>
     */
    #[ORM\OneToMany(targetEntity: Feedbacks::class, mappedBy: 'cnt_id', orphanRemoval: true)]
    private Collection $feedbacks;

    public function __construct()
    {
        $this->ordersContractors = new ArrayCollection();
        $this->projectsGitHubs = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getCntId(): ?int
//    {
//        return $this->cnt_id;
//    }

    public function setCntId(int $cnt_id): static
    {
        $this->id = $cnt_id;

        return $this;
    }

    public function getCntText(): ?string
    {
        return $this->cnt_text;
    }

    public function setCntText(string $cnt_text): static
    {
        $this->cnt_text = $cnt_text;

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
            $ordersContractor->setCntId($this);
        }

        return $this;
    }

    public function removeOrdersContractor(OrdersContractors $ordersContractor): static
    {
        if ($this->ordersContractors->removeElement($ordersContractor)) {
            // set the owning side to null (unless already changed)
            if ($ordersContractor->getCntId() === $this) {
                $ordersContractor->setCntId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectsGitHub>
     */
    public function getProjectsGitHubs(): Collection
    {
        return $this->projectsGitHubs;
    }

    public function addProjectsGitHub(ProjectsGitHub $projectsGitHub): static
    {
        if (!$this->projectsGitHubs->contains($projectsGitHub)) {
            $this->projectsGitHubs->add($projectsGitHub);
            $projectsGitHub->setCntId($this);
        }

        return $this;
    }

    public function removeProjectsGitHub(ProjectsGitHub $projectsGitHub): static
    {
        if ($this->projectsGitHubs->removeElement($projectsGitHub)) {
            // set the owning side to null (unless already changed)
            if ($projectsGitHub->getCntId() === $this) {
                $projectsGitHub->setCntId(null);
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
            $chat->setCntId($this);
        }

        return $this;
    }

    public function removeChat(Chats $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getCntId() === $this) {
                $chat->setCntId(null);
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
            $message->setCntId($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getCntId() === $this) {
                $message->setCntId(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection<int, Messages>
     */

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
            $feedback->setCntId($this);
        }

        return $this;
    }

    public function removeFeedback(Feedbacks $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getCntId() === $this) {
                $feedback->setCntId(null);
            }
        }

        return $this;
    }

}
