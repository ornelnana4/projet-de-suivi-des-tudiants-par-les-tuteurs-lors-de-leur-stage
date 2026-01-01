<?php

namespace App\Repository;

use App\Entity\Visite;
use App\Entity\Etudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visite>
 */
class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }

    /**
     * @return Visite[]
     */
    public function findByEtudiantWithFilters(
        Etudiant $etudiant,
        ?string $statut,
        string $ordre = 'desc'
    ): array {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.etudiant = :etu')
            ->setParameter('etu', $etudiant);

        if ($statut && in_array($statut, ['prévue', 'réalisée', 'annulée'], true)) {
            $qb->andWhere('v.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $ordre = strtolower($ordre) === 'asc' ? 'ASC' : 'DESC';

        $qb->orderBy('v.date', $ordre);

        return $qb->getQuery()->getResult();
    }
}
