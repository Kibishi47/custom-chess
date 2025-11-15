<?php

namespace App\Repository;

use App\Chess\Board\BoardType;
use App\Entity\Game;
use App\Entity\User;
use App\Enum\GameStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findAvailableGame(BoardType $boardType): ?Game
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.gamePlayers', 'gp')
            ->groupBy('g.id')
            ->having('COUNT(gp.id) < 2')
            ->andWhere('g.status = :status')
            ->andWhere('g.boardType = :boardType')
            ->setParameter('status', GameStatus::WAITING->value)
            ->setParameter('boardType', $boardType->getClass())
            ->orderBy('g.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveGameForPlayer(User $user): ?Game
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.gamePlayers', 'gp')
            ->andWhere('gp.player = :player')
            ->andWhere('g.status IN (:statuses)')
            ->setParameter('player', $user)
            ->setParameter('statuses', [
                GameStatus::WAITING->value,
                GameStatus::ONGOING->value,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
