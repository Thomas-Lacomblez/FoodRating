<?php

namespace App\Entity;

use App\Repository\MoyenneProduitsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MoyenneProduitsRepository::class)
 */
class MoyenneProduits
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $moyenne;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $produit_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie_produit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoyenne(): ?float
    {
        return $this->moyenne;
    }

    public function setMoyenne(float $moyenne): self
    {
        $this->moyenne = $moyenne;

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

    public function getCategorieProduit(): ?string
    {
        return $this->categorie_produit;
    }

    public function setCategorieProduit(string $categorie_produit): self
    {
        $this->categorie_produit = $categorie_produit;

        return $this;
    }
}
