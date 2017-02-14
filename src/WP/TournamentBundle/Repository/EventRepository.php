<?php

namespace WP\TournamentBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends \Doctrine\ORM\EntityRepository {

    public function getSearchEvent($page, $nbPerPage, $search, $ispro, $tolerance = 3) {
        $query = $this->createQueryBuilder('e')
                ->leftJoin('e.cover', 'c')
                ->addSelect('c')
                ->leftJoin('e.planning', 'p')
                ->addSelect('p')
                ->where('LEVENSHTEIN(e.title, :search) <= :tolerance')
                ->orWhere('LEVENSHTEIN(e.lieu, :search) <= :tolerance')
                ->andWhere('e.ispro = :ispro')
                ->setParameter('search', $search)
                ->setParameter('ispro', $ispro)
                ->setParameter('tolerance', $tolerance);

        $query->setFirstResult(($page - 1) * $nbPerPage)->setMaxResults($nbPerPage);

        return new Paginator($query, true);
    }

    public function getListEvents($page, $nbPerPage) {
        $query = $this->createQueryBuilder('e')
                ->leftJoin('e.cover', 'c')
                ->addSelect('c')
                ->leftJoin('e.planning', 'p')
                ->addSelect('p')
                ->where('e.date >= :today')
                ->orderBy('e.date', 'asc')
                ->setParameter('today', new \DateTime());

        $query->setFirstResult(($page - 1) * $nbPerPage)->setMaxResults($nbPerPage);

        return new Paginator($query, true);
    }

    public function getNextEvent() {
        $query = $this->createQueryBuilder('e')
                ->leftJoin('e.cover', 'c')
                ->addSelect('c')
                ->leftJoin('e.planning', 'p')
                ->addSelect('p')
                ->where('e.date >= :today')
                ->orderBy('e.date', 'asc')
                ->setParameter('today', new \DateTime());
        return $query->getQuery()->getResult();
    }

}
