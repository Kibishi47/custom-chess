<?php

namespace App\Entity;

use App\Enum\GameStatus;
use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Chess\Board\Board;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['game.info'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['game.info'])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['game.info'])]
    private ?string $result = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\OneToMany(targetEntity: GamePlayer::class, mappedBy: 'game', orphanRemoval: true)]
    #[Groups(['game.info'])]
    private Collection $gamePlayers;

    #[ORM\OneToMany(targetEntity: Move::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $moves;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $boardType = null;

    private ?Board $board = null;
    public array $legalMoves = [];
    public bool $dataSetted = false;
    public array $check = [
        'white' => false,
        'black' => false,
    ];

    public function __construct()
    {
        $this->status = GameStatus::WAITING->value;
        $this->gamePlayers = new ArrayCollection();
        $this->moves = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?GameStatus
    {
        return GameStatus::tryFrom($this->status);
    }

    public function setStatus(GameStatus|string $status): static
    {
        $this->status = $status instanceof GameStatus ? $status->value : $status;
        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getBoardType(): ?string
    {
        return $this->boardType;
    }

    public function setBoardType(string $boardType): static
    {
        $this->boardType = $boardType;
        return $this;
    }

    public function getBoard(): Board
    {
        if (!$this->board) {
            $this->board = Board::createFromGame($this);
        }

        return $this->board;
    }

    public function getGamePlayers(): Collection
    {
        return $this->gamePlayers;
    }

    public function addGamePlayer(GamePlayer $gamePlayer): static
    {
        if ($this->getStatus() !== GameStatus::WAITING) {
            return $this;
        }

        $already = $this->gamePlayers
            ->map(fn (GamePlayer $gp) => $gp->getPlayer())
            ->contains($gamePlayer->getPlayer());

        if ($already) {
            return $this;
        }

        if (!$this->gamePlayers->contains($gamePlayer)) {
            $this->gamePlayers->add($gamePlayer);
            $gamePlayer->setGame($this);
        }

        return $this;
    }

    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(Move $move): static
    {
        if ($this->getStatus() !== GameStatus::ONGOING) {
            $this->setStatus(GameStatus::ONGOING);
            $this->setStartedAt(new \DateTimeImmutable());
        }

        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setGame($this);
        }

        return $this;
    }

    public function getTurnColor(): string
    {
        if ($last = $this->moves->last()) {
            return $last->getOppositeColor();
        }

        return 'white';
    }

    public function start(): void
    {
        if (count($this->gamePlayers) === 2 && $this->status === GameStatus::WAITING->value) {
            $this->setStatus(GameStatus::ONGOING);
            $this->setStartedAt(new \DateTimeImmutable());
        }
    }

    public function cancel(): void
    {
        $this->setStatus(GameStatus::CANCELLED);
        $this->setEndedAt(new \DateTimeImmutable());
    }

    public function finish(): void
    {
        $this->setStatus(GameStatus::FINISHED);
        $this->setEndedAt(new \DateTimeImmutable());
    }

    public function getNextMoveNumber(): int
    {
        return $this->moves->count() + 1;
    }

    public function getRandomColor(array $colors = ['white', 'black']): string
    {
        $used = $this->gamePlayers->map(fn(GamePlayer $gp) => $gp->getColor())->toArray();
        $available = array_values(array_diff($colors, $used));
        return $available[array_rand($available)];
    }

    public function hasLegalMoves(): bool
    {
        return !empty($this->legalMoves);
    }
}
