<?php

namespace App\Entity;

use App\Repository\AmisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AmisRepository::class)
 */
class Amis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="amisUtilisateur1")
     */
    private $utilisateur1;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateurs::class, inversedBy="amisUtilisateur2")
     */
    private $utilisateur2;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=DiscussionPrivee::class, mappedBy="amis", cascade={"remove"})
     */
    private $discussionPrivees;

    /**
     * @ORM\OneToMany(targetEntity=ReponsePrivee::class, mappedBy="amis", cascade={"remove"})
     */
    private $reponsePrivees;

    public function __construct()
    {
        $this->discussionPrivees = new ArrayCollection();
        $this->reponsePrivees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur1(): ?Utilisateurs
    {
        return $this->utilisateur1;
    }

    public function setUtilisateur1(?Utilisateurs $utilisateur1): self
    {
        $this->utilisateur1 = $utilisateur1;

        return $this;
    }

    public function getUtilisateur2(): ?Utilisateurs
    {
        return $this->utilisateur2;
    }

    public function setUtilisateur2(?Utilisateurs $utilisateur2): self
    {
        $this->utilisateur2 = $utilisateur2;

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

    /**
     * @return Collection|DiscussionPrivee[]
     */
    public function getDiscussionPrivees(): Collection
    {
        return $this->discussionPrivees;
    }

    public function addDiscussionPrivee(DiscussionPrivee $discussionPrivee): self
    {
        if (!$this->discussionPrivees->contains($discussionPrivee)) {
            $this->discussionPrivees[] = $discussionPrivee;
            $discussionPrivee->setAmis($this);
        }

        return $this;
    }

    public function removeDiscussionPrivee(DiscussionPrivee $discussionPrivee): self
    {
        if ($this->discussionPrivees->contains($discussionPrivee)) {
            $this->discussionPrivees->removeElement($discussionPrivee);
            // set the owning side to null (unless already changed)
            if ($discussionPrivee->getAmis() === $this) {
                $discussionPrivee->setAmis(null);
            }
        }

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
            $reponsePrivee->setAmis($this);
        }

        return $this;
    }

    public function removeReponsePrivee(ReponsePrivee $reponsePrivee): self
    {
        if ($this->reponsePrivees->contains($reponsePrivee)) {
            $this->reponsePrivees->removeElement($reponsePrivee);
            // set the owning side to null (unless already changed)
            if ($reponsePrivee->getAmis() === $this) {
                $reponsePrivee->setAmis(null);
            }
        }

        return $this;
    }
}
