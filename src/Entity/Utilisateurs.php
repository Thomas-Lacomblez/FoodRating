<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateursRepository")
 * @UniqueEntity(
 * fields= {"username"},
 * message= "Le pseudo que vous avez indiqué est déjà utilisé !"
 * )
 * @UniqueEntity(
 * fields= {"email"},
 * message= "L'adresse email que vous avez indiqué est déjà utilisé !"
 * )
 */
class Utilisateurs implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="4", max="255", minMessage="Pseudo trop court", maxMessage="Pseudo trop long")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Adresse email non conforme")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas entré le même mot de passe")
     */
    public $password_confirmation;

    /**
     * @ORM\OneToMany(targetEntity=Notes::class, mappedBy="utilisateur")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity=Commentaires::class, mappedBy="utilisateur", cascade={"remove"})
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="idUtilisateur")
     */
    private $reponses;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity=Aime::class, mappedBy="idUtilisateur", orphanRemoval=true, cascade={"remove"})
     */
    private $aimes;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $image_base64;

    /**
     * @ORM\OneToMany(targetEntity=Discussion::class, mappedBy="id_utilisateur")
     */
    private $discussions;

    /**
     * @ORM\OneToMany(targetEntity=DemandeAmi::class, mappedBy="demandeur", cascade={"remove"})
     */
    private $demandeAmisDemandeur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeAmi::class, mappedBy="recepteur", cascade={"remove"})
     */
    private $demandeAmisRecepteur;

    /**
     * @ORM\OneToMany(targetEntity=Amis::class, mappedBy="utilisateur1", cascade={"remove"})
     */
    private $amisUtilisateur1;

    /**
     * @ORM\OneToMany(targetEntity=Amis::class, mappedBy="utilisateur2", cascade={"remove"})
     */
    private $amisUtilisateur2;

    /**
     * @ORM\OneToMany(targetEntity=DiscussionPrivee::class, mappedBy="envoyeurDisc", cascade={"remove"})
     */
    private $discussionPriveesEnvoyeur;

    /**
     * @ORM\OneToMany(targetEntity=DiscussionPrivee::class, mappedBy="recepteurDisc", cascade={"remove"})
     */
    private $discussionPriveesRecepteur;

    /**
     * @ORM\OneToMany(targetEntity=ReponsePrivee::class, mappedBy="envoyeurRep", cascade={"remove"})
     */
    private $reponsePriveesEnvoyeur;

    /**
     * @ORM\OneToMany(targetEntity=ReponsePrivee::class, mappedBy="recepteurRep", cascade={"remove"})
     */
    private $reponsePriveesRecepteur;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombreSignalement = 0;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->aimes = new ArrayCollection();
        $this->discussions = new ArrayCollection();
        $this->demandeAmisDemandeur = new ArrayCollection();
        $this->demandeAmisRecepteur = new ArrayCollection();
        $this->amisUtilisateur1 = new ArrayCollection();
        $this->amisUtilisateur2 = new ArrayCollection();
        $this->discussionPriveesEnvoyeur = new ArrayCollection();
        $this->discussionPriveesRecepteur = new ArrayCollection();
        $this->reponsePriveesEnvoyeur = new ArrayCollection();
        $this->reponsePriveesRecepteur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials() {

    }

    public function getSalt() {

    }

    public function getRoles(): ?array {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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
            $note->setUtilisateur($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getUtilisateur() === $this) {
                $note->setUtilisateur(null);
            }
        }

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
            $commentaire->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getUtilisateur() === $this) {
                $commentaire->setUtilisateur(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection|Reponse[]
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setIdUtilisateur($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->contains($reponse)) {
            $this->reponses->removeElement($reponse);
            // set the owning side to null (unless already changed)
            if ($reponse->getIdUtilisateur() === $this) {
                $reponse->setIdUtilisateur(null);
            }
        }

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
            $aime->setIdUtilisateur($this);
        }

        return $this;
    }

    public function removeAime(Aime $aime): self
    {
        if ($this->aimes->contains($aime)) {
            $this->aimes->removeElement($aime);
            // set the owning side to null (unless already changed)
            if ($aime->getIdUtilisateur() === $this) {
                $aime->setIdUtilisateur(null);
            }
        }
    }
    
    /**
     * @return Collection|Discussion[]
     */
    public function getDiscussions(): Collection
    {
        return $this->discussions;
    }

    public function getImageBase64(): ?string
    {
        return $this->image_base64;
    }

    public function setImageBase64(?string $image_base64): self
    {
        $this->image_base64 = $image_base64;
        
        return $this;
    }

    /**
     * @return Collection|DemandeAmi[]
     */
    public function getDemandeAmisDemandeur(): Collection
    {
        return $this->demandeAmisDemandeur;
    }

    public function addDemandeAmisDemandeur(DemandeAmi $demandeAmisDemandeur): self
    {
        if (!$this->demandeAmisDemandeur->contains($demandeAmisDemandeur)) {
            $this->demandeAmisDemandeur[] = $demandeAmisDemandeur;
            $demandeAmisDemandeur->setDemandeur($this);
        }

        return $this;
    }

    public function removeDemandeAmisDemandeur(DemandeAmi $demandeAmisDemandeur): self
    {
        if ($this->demandeAmisDemandeur->contains($demandeAmisDemandeur)) {
            $this->demandeAmisDemandeur->removeElement($demandeAmisDemandeur);
            // set the owning side to null (unless already changed)
            if ($demandeAmisDemandeur->getDemandeur() === $this) {
                $demandeAmisDemandeur->setDemandeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DemandeAmi[]
     */
    public function getDemandeAmisRecepteur(): Collection
    {
        return $this->demandeAmisRecepteur;
    }

    public function addDemandeAmisRecepteur(DemandeAmi $demandeAmisRecepteur): self
    {
        if (!$this->demandeAmisRecepteur->contains($demandeAmisRecepteur)) {
            $this->demandeAmisRecepteur[] = $demandeAmisRecepteur;
            $demandeAmisRecepteur->setRecepteur($this);
        }

        return $this;
    }

    public function removeDemandeAmisRecepteur(DemandeAmi $demandeAmisRecepteur): self
    {
        if ($this->demandeAmisRecepteur->contains($demandeAmisRecepteur)) {
            $this->demandeAmisRecepteur->removeElement($demandeAmisRecepteur);
            // set the owning side to null (unless already changed)
            if ($demandeAmisRecepteur->getRecepteur() === $this) {
                $demandeAmisRecepteur->setRecepteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Amis[]
     */
    public function getAmisUtilisateur1(): Collection
    {
        return $this->amisUtilisateur1;
    }

    public function addAmisUtilisateur1(Amis $amisUtilisateur1): self
    {
        if (!$this->amisUtilisateur1->contains($amisUtilisateur1)) {
            $this->amisUtilisateur1[] = $amisUtilisateur1;
            $amisUtilisateur1->setUtilisateur1($this);
        }

        return $this;
    }

    public function removeAmisUtilisateur1(Amis $amisUtilisateur1): self
    {
        if ($this->amisUtilisateur1->contains($amisUtilisateur1)) {
            $this->amisUtilisateur1->removeElement($amisUtilisateur1);
            // set the owning side to null (unless already changed)
            if ($amisUtilisateur1->getUtilisateur1() === $this) {
                $amisUtilisateur1->setUtilisateur1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Amis[]
     */
    public function getAmisUtilisateur2(): Collection
    {
        return $this->amisUtilisateur2;
    }

    public function addAmisUtilisateur2(Amis $amisUtilisateur2): self
    {
        if (!$this->amisUtilisateur2->contains($amisUtilisateur2)) {
            $this->amisUtilisateur2[] = $amisUtilisateur2;
            $amisUtilisateur2->setUtilisateur2($this);
        }

        return $this;
    }

    public function removeAmisUtilisateur2(Amis $amisUtilisateur2): self
    {
        if ($this->amisUtilisateur2->contains($amisUtilisateur2)) {
            $this->amisUtilisateur2->removeElement($amisUtilisateur2);
            // set the owning side to null (unless already changed)
            if ($amisUtilisateur2->getUtilisateur2() === $this) {
                $amisUtilisateur2->setUtilisateur2(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DiscussionPrivee[]
     */
    public function getDiscussionPriveesEnvoyeur(): Collection
    {
        return $this->discussionPriveesEnvoyeur;
    }

    public function addDiscussionPriveesEnvoyeur(DiscussionPrivee $discussionPriveesEnvoyeur): self
    {
        if (!$this->discussionPriveesEnvoyeur->contains($discussionPriveesEnvoyeur)) {
            $this->discussionPriveesEnvoyeur[] = $discussionPriveesEnvoyeur;
            $discussionPriveesEnvoyeur->setEnvoyeurDisc($this);
        }

        return $this;
    }

    public function removeDiscussionPriveesEnvoyeur(DiscussionPrivee $discussionPriveesEnvoyeur): self
    {
        if ($this->discussionPriveesEnvoyeur->contains($discussionPriveesEnvoyeur)) {
            $this->discussionPriveesEnvoyeur->removeElement($discussionPriveesEnvoyeur);
            // set the owning side to null (unless already changed)
            if ($discussionPriveesEnvoyeur->getEnvoyeurDisc() === $this) {
                $discussionPriveesEnvoyeur->setEnvoyeurDisc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DiscussionPrivee[]
     */
    public function getDiscussionPriveesRecepteur(): Collection
    {
        return $this->discussionPriveesRecepteur;
    }

    public function addDiscussionPriveesRecepteur(DiscussionPrivee $discussionPriveesRecepteur): self
    {
        if (!$this->discussionPriveesRecepteur->contains($discussionPriveesRecepteur)) {
            $this->discussionPriveesRecepteur[] = $discussionPriveesRecepteur;
            $discussionPriveesRecepteur->setRecepteurDisc($this);
        }

        return $this;
    }

    public function removeDiscussionPriveesRecepteur(DiscussionPrivee $discussionPriveesRecepteur): self
    {
        if ($this->discussionPriveesRecepteur->contains($discussionPriveesRecepteur)) {
            $this->discussionPriveesRecepteur->removeElement($discussionPriveesRecepteur);
            // set the owning side to null (unless already changed)
            if ($discussionPriveesRecepteur->getRecepteurDisc() === $this) {
                $discussionPriveesRecepteur->setRecepteurDisc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReponsePrivee[]
     */
    public function getReponsePriveesEnvoyeur(): Collection
    {
        return $this->reponsePriveesEnvoyeur;
    }

    public function addReponsePriveesEnvoyeur(ReponsePrivee $reponsePriveesEnvoyeur): self
    {
        if (!$this->reponsePriveesEnvoyeur->contains($reponsePriveesEnvoyeur)) {
            $this->reponsePriveesEnvoyeur[] = $reponsePriveesEnvoyeur;
            $reponsePriveesEnvoyeur->setEnvoyeurRep($this);
        }

        return $this;
    }

    public function removeReponsePriveesEnvoyeur(ReponsePrivee $reponsePriveesEnvoyeur): self
    {
        if ($this->reponsePriveesEnvoyeur->contains($reponsePriveesEnvoyeur)) {
            $this->reponsePriveesEnvoyeur->removeElement($reponsePriveesEnvoyeur);
            // set the owning side to null (unless already changed)
            if ($reponsePriveesEnvoyeur->getEnvoyeurRep() === $this) {
                $reponsePriveesEnvoyeur->setEnvoyeurRep(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReponsePrivee[]
     */
    public function getReponsePriveesRecepteur(): Collection
    {
        return $this->reponsePriveesRecepteur;
    }

    public function addReponsePriveesRecepteur(ReponsePrivee $reponsePriveesRecepteur): self
    {
        if (!$this->reponsePriveesRecepteur->contains($reponsePriveesRecepteur)) {
            $this->reponsePriveesRecepteur[] = $reponsePriveesRecepteur;
            $reponsePriveesRecepteur->setRecepteurRep($this);
        }

        return $this;
    }

    public function removeReponsePriveesRecepteur(ReponsePrivee $reponsePriveesRecepteur): self
    {
        if ($this->reponsePriveesRecepteur->contains($reponsePriveesRecepteur)) {
            $this->reponsePriveesRecepteur->removeElement($reponsePriveesRecepteur);
            // set the owning side to null (unless already changed)
            if ($reponsePriveesRecepteur->getRecepteurRep() === $this) {
                $reponsePriveesRecepteur->setRecepteurRep(null);
            }
        }

        return $this;
    }

    public function getNombreSignalement(): ?int
    {
        return $this->nombreSignalement;
    }

    public function setNombreSignalement(int $nombreSignalement): self
    {
        $this->nombreSignalement = $nombreSignalement;

        return $this;
    }
}