<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quizz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private $quizz;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    private $OptionA;

    #[ORM\Column(type: 'string', length: 255)]
    private $OptionB;

    #[ORM\Column(type: 'string', length: 255)]
    private $OptionC;

    #[ORM\Column(type: 'string', length: 255)]
    private $OptionD;

    #[ORM\Column(type: 'string', length: 1)]
    private $correct_option;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizz(): ?Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(?Quizz $quizz): self
    {
        $this->quizz = $quizz;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getOptionA(): ?string
    {
        return $this->OptionA;
    }

    public function setOptionA(string $OptionA): self
    {
        $this->OptionA = $OptionA;
        return $this;
    }

    public function getOptionB(): ?string
    {
        return $this->OptionB;
    }

    public function setOptionB(string $OptionB): self
    {
        $this->OptionB = $OptionB;
        return $this;
    }

    public function getOptionC(): ?string
    {
        return $this->OptionC;
    }

    public function setOptionC(string $OptionC): self
    {
        $this->OptionC = $OptionC;
        return $this;
    }

    public function getOptionD(): ?string
    {
        return $this->OptionD;
    }

    public function setOptionD(string $OptionD): self
    {
        $this->OptionD = $OptionD;
        return $this;
    }

    public function getCorrectOption(): ?string
    {
        return $this->correct_option;
    }
    
    public function setCorrectOption(string $correct_option): self
    {
        $this->correct_option = $correct_option;
        return $this;
    }
    
}
