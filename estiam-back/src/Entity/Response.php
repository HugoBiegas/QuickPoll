<?php

namespace App\Entity;

use App\Repository\ResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
class Response
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quizz::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $quizz; 

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $question;

    #[ORM\Column(type: 'string', length: 1)]
    private $selectedOption;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getQuizz(): ?Quizz {
        return $this->quizz;
    }

    public function setQuizz(?Quizz $quizz): self {
        $this->quizz = $quizz;
        return $this;
    }

    public function getQuestion(): ?Question {
        return $this->question;
    }

    public function setQuestion(?Question $question): self {
        $this->question = $question;
        return $this;
    }

    public function getSelectedOption(): ?string {
        return $this->selectedOption;
    }

    public function setSelectedOption(string $selectedOption): self {
        $this->selectedOption = $selectedOption;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface {
        return $this->createdAt;
    }

    // Note: pas de setter pour createdAt car la date est définie lors de la création de l'entité
}
