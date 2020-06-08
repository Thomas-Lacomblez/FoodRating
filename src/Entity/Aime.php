<?php

namespace App\Entity;

use App\Repository\AimeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AimeRepository::class)
 */
class Aime
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="aimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idUtilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Commentaires::class, inversedBy="aimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idCommentaire;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $produit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUtilisateur(): ?Utilisateurs
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?Utilisateurs $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;

        return $this;
    }

    public function getIdCommentaire(): ?Commentaires
    {
        return $this->idCommentaire;
    }

    public function setIdCommentaire(?Commentaires $idCommentaire): self
    {
        $this->idCommentaire = $idCommentaire;

        return $this;
    }

    public function getProduit(): ?string
    {
        return $this->produit;
    }

    public function setProduit(string $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
}
