<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reponses
 * @ORM\Entity(repositoryClass="App\Repository\ResponseRepository")
 * @ORM\Table(name="reponses", indexes={@ORM\Index(name="I_FK_REPONSES_DISCUSSION", columns={"id_discussion"}), @ORM\Index(name="I_FK_REPONSES_UTILISATEURS", columns={"id_utilisateur"})})
 */
class Reponses
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_reponse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReponse;

    /**
     * @var int
     *
     * @ORM\Column(name="id_utilisateur", type="integer", nullable=false)
     */
    private $idUtilisateur;

    /**
     * @var int
     *
     * @ORM\Column(name="id_discussion", type="integer", nullable=false)
     */
    private $idDiscussion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="string", length=32, nullable=true, options={"fixed"=true})
     */
    private $message;

    public function getIdReponse(): ?int
    {
        return $this->idReponse;
    }

    public function getIdUtilisateur(): ?int
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(int $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;

        return $this;
    }

    public function getIdDiscussion(): ?int
    {
        return $this->idDiscussion;
    }

    public function setIdDiscussion(int $idDiscussion): self
    {
        $this->idDiscussion = $idDiscussion;

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


}
