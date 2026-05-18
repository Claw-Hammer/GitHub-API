<?php

namespace App\Entity;

use App\Repository\GithubPhpProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GithubPhpProjectRepository::class)]
#[ORM\Table(name: 'github_php_projects')]
class GithubPhpProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $repositoryId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastPushDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $stars = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepositoryId(): ?int
    {
        return $this->repositoryId;
    }

    public function setRepositoryId(int $repositoryId): static
    {
        $this->repositoryId = $repositoryId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getLastPushDate(): ?\DateTimeImmutable
    {
        return $this->lastPushDate;
    }

    public function setLastPushDate(?\DateTimeImmutable $lastPushDate): static
    {
        $this->lastPushDate = $lastPushDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): static
    {
        $this->stars = $stars;

        return $this;
    }
}
