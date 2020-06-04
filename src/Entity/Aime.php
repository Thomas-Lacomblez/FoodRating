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
     * @ORM\Column(type="integer")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="integer")
     */
    private $commentaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?int
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(int $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getCommentaire(): ?int
    {
        return $this->commentaire;
    }

    public function setCommentaire(int $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
