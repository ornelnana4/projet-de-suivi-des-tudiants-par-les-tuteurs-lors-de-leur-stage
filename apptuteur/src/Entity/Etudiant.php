<?php

namespace App\Entity;

use App\Entity\Tuteur;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
#[ApiResource]
class Etudiant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $formation = null;
    #[ORM\ManyToOne(targetEntity: Tuteur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tuteur $tuteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getFormation(): ?string
    {
        return $this->formation;
    }

    public function setFormation(string $formation): static
    {
        $this->formation = $formation;

        return $this;
    }
    public function getTuteur(): ?Tuteur
    {
        return $this->tuteur;
    }

    public function setTuteur(Tuteur $tuteur): static
    {
        $this->tuteur = $tuteur;
        return $this;
    }
}
