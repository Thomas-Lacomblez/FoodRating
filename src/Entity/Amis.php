<?php

namespace App\Entity;

use App\Repository\AmisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AmisRepository::class)
 */
class Amis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="amisUtilisateur1")
     */
    private $utilisateur1;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="amisUtilisateur2")
     */
    private $utilisateur2;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur1(): ?Utilisateurs
    {
        return $this->utilisateur1;
    }

    public function setUtilisateur1(?Utilisateurs $utilisateur1): self
    {
        $this->utilisateur1 = $utilisateur1;

        return $this;
    }

    public function getUtilisateur2(): ?Utilisateurs
    {
        return $this->utilisateur2;
    }

    public function setUtilisateur2(?Utilisateurs $utilisateur2): self
    {
        $this->utilisateur2 = $utilisateur2;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
