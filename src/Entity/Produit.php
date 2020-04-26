<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProduitRepository")
 */
class Produit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="text")
     */
    private $ingredient;

    /**
     * @ORM\Column(type="text")
     */
    private $trace;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $marque;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $distributeur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etiquette;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $kcal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $additif;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $nutriscore;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commentaires", mappedBy="produit")
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notes", mappedBy="produit", orphanRemoval=true)
     */
    private $notes;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getIngredient(): ?string
    {
        return $this->ingredient;
    }

    public function setIngredient(string $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getTrace(): ?string
    {
        return $this->trace;
    }

    public function setTrace(string $trace): self
    {
        $this->trace = $trace;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDistributeur(): ?string
    {
        return $this->distributeur;
    }

    public function setDistributeur(string $distributeur): self
    {
        $this->distributeur = $distributeur;

        return $this;
    }

    public function getEtiquette(): ?string
    {
        return $this->etiquette;
    }

    public function setEtiquette(?string $etiquette): self
    {
        $this->etiquette = $etiquette;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getKcal(): ?string
    {
        return $this->kcal;
    }

    public function setKcal(string $kcal): self
    {
        $this->kcal = $kcal;

        return $this;
    }

    public function getAdditif(): ?string
    {
        return $this->additif;
    }

    public function setAdditif(?string $additif): self
    {
        $this->additif = $additif;

        return $this;
    }

    public function getNutriscore(): ?string
    {
        return $this->nutriscore;
    }

    public function setNutriscore(?string $nutriscore): self
    {
        $this->nutriscore = $nutriscore;

        return $this;
    }

    /**
     * @return Collection|Commentaires[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaires $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setProduit($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getProduit() === $this) {
                $commentaire->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notes[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setProduit($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getProduit() === $this) {
                $note->setProduit(null);
            }
        }

        return $this;
    }
}
