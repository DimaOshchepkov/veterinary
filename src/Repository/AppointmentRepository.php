<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findByOwnerQueryBuilder(User $owner, bool $active): \Doctrine\ORM\QueryBuilder
    {
        $statuses = array_map(
            fn (AppointmentStatus $s) => $s->value,
            $active ? AppointmentStatus::activeCases() : AppointmentStatus::inactiveCases(),
        );
        $dir = $active ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('a')
            ->addSelect('p', 's')
            ->join('a.pet', 'p')
            ->join('a.service', 's')
            ->andWhere('a.owner = :owner')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', $statuses)
            ->orderBy('a.appointmentDate', $dir)
            ->addOrderBy('a.appointmentTime', $dir)
            ->addOrderBy('a.id', $dir);
    }

    /** @return Pagerfanta<Appointment> */
    public function paginateByOwner(User $owner, bool $active, int $page, int $limit): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter(
            $this->findByOwnerQueryBuilder($owner, $active),
            fetchJoinCollection: false,
            useOutputWalkers: false,
        ));

        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function ownerHasAny(User $owner): bool
    {
        return $this->count(['owner' => $owner]) > 0;
    }
    public function save(Appointment $appointment, bool $flush = true): void
    {
        $this->getEntityManager()->persist($appointment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
