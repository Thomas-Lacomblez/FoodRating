<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Discussion
 * @ORM\Entity(repositoryClass="App\Repository\DiscussionRepository")
 * @ORM\Table(name="discussion", indexes={@ORM\Index(name="I_FK_DISCUSSION_UTILISATEURS", columns={"id_utilisateur"})})
 */
class Discussion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_discussion", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDiscussion;

    /**
     * @ORM\OneToOne(targetEntity="Utilisateurs")
     * @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     */
    private $id_utilisateur;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sujet", type="string", length=32, nullable=true, options={"fixed"=true})
     */
    private $sujet;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true, options={"fixed"=true})
     */
    private $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="titre", type="string", length=32, nullable=true, options={"fixed"=true})
     */
    private $titre;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="creation", type="datetime", nullable=true)
     */
    private $creation;

    public function getIdDiscussion(): ?int
    {
        return $this->idDiscussion;
    }

    public function getId_utilisateur(): ?Utilisateurs
    {
        return $this->id_utilisateur;
    }

    public function setId_utilisateur(Utilisateurs $Utilisateur): self
    {
        $this->id_utilisateur = $Utilisateur;

        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(?string $sujet): self
    {
        $this->sujet = $sujet;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getCreation(): ?\DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(?\DateTimeInterface $creation): self
    {
        $this->creation = $creation;

        return $this;
    }


}
