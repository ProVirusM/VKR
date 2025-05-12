<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("user:read")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups("user:read")]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'usr_id', cascade: ['persist', 'remove'])]
    private ?Contractors $contractors = null;

    #[ORM\OneToOne(mappedBy: 'usr_id', cascade: ['persist', 'remove'])]
    private ?Customers $customers = null;

    #[ORM\Column(length: 255)]
    private ?string $usr_name = null;

    #[ORM\Column(length: 255)]
    private ?string $usr_surname = null;

    #[ORM\Column(length: 255)]
    private ?string $usr_patronymic = null;
    #[ORM\Column(type: 'json', options: ['default' => '["customer"]'])]
    private array $roles = [];
    // Геттер для поля id (аннотация @Groups здесь не обязательна, если она уже указана над свойством)
    public function getId(): ?int
    {
        return $this->id;
    }

    // Геттер для поля email
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Сеттер для поля email
    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    // Геттер для поля password
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Сеттер для поля password
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getContractors(): ?Contractors
    {
        return $this->contractors;
    }

    public function setContractors(Contractors $contractors): static
    {
        // set the owning side of the relation if necessary
        if ($contractors->getUsrId() !== $this) {
            $contractors->setUsrId($this);
        }

        $this->contractors = $contractors;

        return $this;
    }

    public function getCustomers(): ?Customers
    {
        return $this->customers;
    }

    public function setCustomers(Customers $customers): static
    {
        // set the owning side of the relation if necessary
        if ($customers->getUsrId() !== $this) {
            $customers->setUsrId($this);
        }

        $this->customers = $customers;

        return $this;
    }

    public function getUsrName(): ?string
    {
        return $this->usr_name;
    }

    public function setUsrName(string $usr_name): static
    {
        $this->usr_name = $usr_name;

        return $this;
    }

    public function getUsrSurname(): ?string
    {
        return $this->usr_surname;
    }

    public function setUsrSurname(string $usr_surname): static
    {
        $this->usr_surname = $usr_surname;

        return $this;
    }

    public function getUsrPatronymic(): ?string
    {
        return $this->usr_patronymic;
    }

    public function setUsrPatronymic(string $usr_patronymic): static
    {
        $this->usr_patronymic = $usr_patronymic;

        return $this;
    }
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Реализация интерфейса UserInterface
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Гарантируем, что у пользователя есть хотя бы одна роль
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return $roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Если хранишь какой-либо временный пароль или токен — очищай здесь.
    }
}
