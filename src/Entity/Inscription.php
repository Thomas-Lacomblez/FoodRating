<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InscriptionRepository")
 */
class Inscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, max=255, minMessage="Pseudo non valide", maxMessage="Pseudo non valide")
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Email non valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, max=255, minMessage="Mot de passe non valide", maxMessage="Mot de passe non valide")
     */
    private $mdp;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, max=255, minMessage="Mot de passe non valide", maxMessage="Mot de passe non valide")
     */
    private $mdp_confirmation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function getMdpConfirmation(): ?string
    {
        return $this->mdp_confirmation;
    }

    public function setMdpConfirmation(string $mdp_confirmation): self
    {
        $this->mdp_confirmation = $mdp_confirmation;

        return $this;
    }
}
