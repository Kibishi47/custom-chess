<?php

namespace App\Entity;

use App\Enum\GameStatus;
use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?string $result = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    /**
     * @var Collection<int, GamePlayer>
     */
    #[ORM\OneToMany(targetEntity: GamePlayer::class, mappedBy: 'game', orphanRemoval: true)]
    #[Groups(['game.info'])]
    private Collection $gamePlayers;

    /**
     * @var Collection<int, Move>
     */
    #[ORM\OneToMany(targetEntity: Move::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $moves;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->status = GameStatus::WAITING->value;
        $this->gamePlayers = new ArrayCollection();
        $this->moves = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    /*
     * METHODS
     */
    public function getRandomColor(array $colors = ['white', 'black']): string
    {
        $usedColors = $this->getGamePlayers()->map(fn(GamePlayer $gp) => $gp->getColor())->toArray();
        $available = array_values(array_diff($colors, $usedColors));

        return $available[array_rand($available)];
    }

    public function start(): void
    {
        if (!$this->isReadyToStart()) {
            return;
        }

        $this->setStatus(GameStatus::ONGOING);
        $this->setStartedAt(new \DateTimeImmutable());
    }

    public function isReadyToStart(): bool
    {
        return $this->getStatus() === GameStatus::WAITING
            && count($this->getGamePlayers()) === 2;
    }

    public function finish(): void
    {
        $this->setStatus(GameStatus::FINISHED);
        $this->setEndedAt(new \DateTimeImmutable());
    }

    public function cancel(): void
    {
        $this->setStatus(GameStatus::CANCELLED);
        $this->setEndedAt(new \DateTimeImmutable());
    }

    /*
     * GETTERS AND SETTERS
     */
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

    /**
     * @return Collection<int, GamePlayer>
     */
    public function getGamePlayers(): Collection
    {
        return $this->gamePlayers;
    }

    public function addGamePlayer(GamePlayer $gamePlayer): static
    {
        /**
         * We can add a player only during waiting status
         */
        if ($this->getStatus() !== GameStatus::WAITING) {
            return $this;
        }

        /*
         * If a player is already in this game so we don't add it
         */
        if ($this->gamePlayers
                ->map(function (GamePlayer $gp) {
                    return $gp->getPlayer();
                })
                ->contains($gamePlayer->getPlayer())
        ) {
            return $this;
        }

        if (!$this->gamePlayers->contains($gamePlayer)) {
            $this->gamePlayers->add($gamePlayer);
            $gamePlayer->setGame($this);
        }

        return $this;
    }

    /**
     * @deprecated Removing players is not allowed for now
     */
    public function removeGamePlayer(GamePlayer $gamePlayer): static
    {
        throw new \LogicException('Cannot remove a player to a game');

        if ($this->gamePlayers->removeElement($gamePlayer)) {
            // set the owning side to null (unless already changed)
            if ($gamePlayer->getGame() === $this) {
                $gamePlayer->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Move>
     */
    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(Move $move): static
    {
        /**
         * We can only add a move during ongoing status
         */
        if ($this->getStatus() !== GameStatus::ONGOING) {
            throw new \LogicException('Cannot add a move to a game that is not ongoing');
        }

        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setGame($this);
        }

        return $this;
    }

    public function removeMove(Move $move): static
    {
        /**
         * We can only remove a move during ongoing status
         */
        if ($this->getStatus() !== GameStatus::ONGOING) {
            throw new \LogicException('Cannot remove a move to a game that is not ongoing');
        }

        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getGame() === $this) {
                $move->setGame(null);
            }
        }

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
}
