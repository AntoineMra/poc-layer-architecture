<?php

namespace App\Repository;

use App\Entity\BankTranslation;
use App\Entity\Enum\TransactionStatus;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<BankTranslation>
 *
 * @method BankTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankTranslation[]    findAll()
 * @method BankTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankTranslation::class);
    }

    public function save(BankTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BankTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function isLabelParsed(string $label, TransactionStatus $status): ?BankTranslation
    {
        $expr = $this->_em->getExpressionBuilder();
        $translation = $this
            ->createQueryBuilder('bt')
            ->select('bt')
            ->where($expr->eq('bt.bankLabel', ':label'))
            ->setParameter(':label', $label)
            ->andWhere($expr->eq('bt.status', ':status'))
            ->setParameter(':status', $status->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $translation;
    }
}
