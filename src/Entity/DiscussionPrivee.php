<?php

namespace App\Entity;

use App\Repository\DiscussionPriveeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscussionPriveeRepository::class)
 */
class DiscussionPrivee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Amis::class, inversedBy="discussionPrivees")
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
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="discussionPriveesEnvoyeur")
     */
    private $envoyeurDisc;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="discussionPriveesRecepteur")
     */
    private $recepteurDisc;

    /**
     * @ORM\OneToMany(targetEntity=ReponsePrivee::class, mappedBy="discussion", cascade={"remove"})
     */
    private $reponsePrivees;

    public function __construct()
    {
        $this->reponsePrivees = new ArrayCollection();
    }

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

    public function getEnvoyeurDisc(): ?Utilisateurs
    {
        return $this->envoyeurDisc;
    }

    public function setEnvoyeurDisc(?Utilisateurs $envoyeurDisc): self
    {
        $this->envoyeurDisc = $envoyeurDisc;

        return $this;
    }

    public function getRecepteurDisc(): ?Utilisateurs
    {
        return $this->recepteurDisc;
    }

    public function setRecepteurDisc(?Utilisateurs $recepteurDisc): self
    {
        $this->recepteurDisc = $recepteurDisc;

        return $this;
    }

    /**
     * @return Collection|ReponsePrivee[]
     */
    public function getReponsePrivees(): Collection
    {
        return $this->reponsePrivees;
    }

    public function addReponsePrivee(ReponsePrivee $reponsePrivee): self
    {
        if (!$this->reponsePrivees->contains($reponsePrivee)) {
            $this->reponsePrivees[] = $reponsePrivee;
            $reponsePrivee->setDiscussion($this);
        }

        return $this;
    }

    public function removeReponsePrivee(ReponsePrivee $reponsePrivee): self
    {
        if ($this->reponsePrivees->contains($reponsePrivee)) {
            $this->reponsePrivees->removeElement($reponsePrivee);
            // set the owning side to null (unless already changed)
            if ($reponsePrivee->getDiscussion() === $this) {
                $reponsePrivee->setDiscussion(null);
            }
        }

        return $this;
    }
}
