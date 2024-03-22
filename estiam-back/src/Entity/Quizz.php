<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\QuizzRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Question;

#[ORM\Entity(repositoryClass: QuizzRepository::class)]
class Quizz
{
    #[Groups(['quizz_details', 'quizz_list'])]
    #[ORM\Id]
    #[ORM\Column(type:"string", length:36, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id;

    #[Groups(['quizz_details', 'quizz_list'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(['quizz_details'])]
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quizz', orphanRemoval: true, cascade: ['persist'])]
    private Collection $questions;

    public function __construct() {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function setUser(?User $user): self {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection {
        return $this->questions;
    }

    public function addQuestions(Question $questions): self {
        if (!$this->questions->contains($questions)) {
            $this->questions[] = $questions;
            $questions->setQuizz($this);
        }
        return $this;
    }

    public function removeQuestions(Question $questions): self {
        if ($this->questions->removeElement($questions)) {
            // set the owning side to null (unless already changed)
            if ($questions->getQuizz() === $this) {
                $questions->setQuizz(null);
            }
        }
        return $this;
    }
}
