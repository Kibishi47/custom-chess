<?php

namespace App\Entity;

use App\Repository\GamePlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GamePlayerRepository::class)]
class GamePlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['matchmaking'])]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'gamePlayers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(inversedBy: 'gamePlayers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['matchmaking'])]
    private ?User $player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;

        return $this;
    }
}
