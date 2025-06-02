<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    // Méthode pour récupérer toutes les transactions d'un utilisateur
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Méthode pour récupérer les transactions par type (revenu ou dépense)
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    // Méthode pour calculer le solde total d'un utilisateur
    public function calculateBalance(int $userId): float
    {
        $incomes = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.type = :type')
            ->andWhere('t.user = :user')
            ->setParameter('type', 'income')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        $expenses = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.type = :type')
            ->andWhere('t.user = :user')
            ->setParameter('type', 'expense')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float)$incomes - (float)$expenses;
    }
}