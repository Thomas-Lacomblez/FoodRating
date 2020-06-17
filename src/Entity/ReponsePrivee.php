<?php

namespace App\Entity;

use App\Repository\ReponsePriveeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReponsePriveeRepository::class)
 */
class ReponsePrivee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Amis::class, inversedBy="reponsePrivees")
     */
    private $amis;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=DiscussionPrivee::class, inversedBy="reponsePrivees")
     */
    private $discussion;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="reponsePriveesEnvoyeur")
     */
    private $envoyeurRep;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="reponsePriveesRecepteur")
     */
    private $recepteurRep;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmis(): ?Amis
    {
        return $this->amis;
    }

    public function setAmis(?Amis $amis): self
    {
        $this->amis = $amis;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDiscussion(): ?DiscussionPrivee
    {
        return $this->discussion;
    }

    public function setDiscussion(?DiscussionPrivee $discussion): self
    {
        $this->discussion = $discussion;

        return $this;
    }

    public function getEnvoyeurRep(): ?Utilisateurs
    {
        return $this->envoyeurRep;
    }

    public function setEnvoyeurRep(?Utilisateurs $envoyeurRep): self
    {
        $this->envoyeurRep = $envoyeurRep;

        return $this;
    }

    public function getRecepteurRep(): ?Utilisateurs
    {
        return $this->recepteurRep;
    }

    public function setRecepteurRep(?Utilisateurs $recepteurRep): self
    {
        $this->recepteurRep = $recepteurRep;

        return $this;
    }
}
