<?php

namespace App\Entity;

use App\Repository\CommentairesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentairesRepository::class)
 */
class Commentaires
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $produit_id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="commentaires")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $utile;

    /**
     * @ORM\OneToMany(targetEntity=Aime::class, mappedBy="idCommentaire", orphanRemoval=true)
     */
    private $aimes;

    public function __construct()
    {
        $this->aimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getProduitId(): ?string
    {
        return $this->produit_id;
    }

    public function setProduitId(string $produit_id): self
    {
        $this->produit_id = $produit_id;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateurs
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateurs $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getUtile(): ?int
    {
        return $this->utile;
    }

    public function setUtile(?int $utile): self
    {
        $this->utile = $utile;

        return $this;
    }

    /**
     * @return Collection|Aime[]
     */
    public function getAimes(): Collection
    {
        return $this->aimes;
    }

    public function addAime(Aime $aime): self
    {
        if (!$this->aimes->contains($aime)) {
            $this->aimes[] = $aime;
            $aime->setIdCommentaire($this);
        }

        return $this;
    }

    public function removeAime(Aime $aime): self
    {
        if ($this->aimes->contains($aime)) {
            $this->aimes->removeElement($aime);
            // set the owning side to null (unless already changed)
            if ($aime->getIdCommentaire() === $this) {
                $aime->setIdCommentaire(null);
            }
        }

        return $this;
    }
}
