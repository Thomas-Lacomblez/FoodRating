<?php

namespace App\Entity;

use App\Repository\DemandeAmiRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeAmiRepository::class)
 */
class DemandeAmi
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="demandeAmisDemandeur")
     */
    private $demandeur;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="demandeAmisRecepteur")
     */
    private $recepteur;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemandeur(): ?Utilisateurs
    {
        return $this->demandeur;
    }

    public function setDemandeur(?Utilisateurs $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    public function getRecepteur(): ?Utilisateurs
    {
        return $this->recepteur;
    }

    public function setRecepteur(?Utilisateurs $recepteur): self
    {
        $this->recepteur = $recepteur;

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
