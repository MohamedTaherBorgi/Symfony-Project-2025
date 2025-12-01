<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateTime = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixTotal = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: 'panier')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(targetEntity: LignePanier::class, mappedBy: 'panier', cascade: ['remove'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->dateTime = new \DateTime();
        $this->prixTotal = 0.0;
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(float $prixTotal): self
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LignePanier $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setPanier($this);
        }
        return $this;
    }

    public function removeLigne(LignePanier $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getPanier() === $this) {
                $ligne->setPanier(null);
            }
        }
        return $this;
    }

    public function ajouterProduit(): void
    {
        // Logique pour ajouter un produit
    }

    public function retirerProduit(): void
    {
        // Logique pour retirer un produit
    }

    public function viderPanier(): void
    {
        // Logique pour vider le panier
    }

    public function calculerTotal(): void
    {
        // Logique pour calculer le total
    }

    public function validerCommande(): void
    {
        // Logique pour valider la commande
    }
}
