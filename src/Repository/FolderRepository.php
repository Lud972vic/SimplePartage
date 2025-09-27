<?php

namespace App\Repository;

use App\Entity\Folder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Folder>
 *
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
    /**
     * FolderRepository constructor.
     *
     * @param ManagerRegistry $registry The manager registry.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    /**
     * Persists a Folder entity to the database.
     *
     * @param Folder $entity The Folder entity to save.
     * @param bool $flush Whether to flush the entity manager after persisting.
     */
    public function save(Folder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes a Folder entity from the database.
     *
     * @param Folder $entity The Folder entity to remove.
     * @param bool $flush Whether to flush the entity manager after removing.
     */
    public function remove(Folder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Finds all root folders (folders that do not have a parent).
     *
     * @return Folder[] An array of Folder objects.
     */
    public function findRootFolders(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.parent IS NULL')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all folders that are accessible to a specific user.
     *
     * This method checks for permissions granted directly to the user or to any groups
     * the user is a member of.
     *
     * @param User $user The user for whom to check permissions.
     * @return Folder[] An array of accessible Folder objects.
     */
    public function findAccessibleFolders(User $user): array
    {
        $userGroups = $user->getGroups()->map(fn($group) => $group->getId())->toArray();

        $qb = $this->createQueryBuilder('f');
        $expr = $qb->expr();

        $userPermission = $expr->andX(
            $expr->eq('p.targetType', ':userType'),
            $expr->eq('p.targetId', ':userId'),
            $expr->eq('p.canViewFolder', ':canView')
        );

        $groupPermission = '1=0'; // Default to a condition that is always false
        if (!empty($userGroups)) {
            $groupPermission = $expr->andX(
                $expr->eq('p.targetType', ':groupType'),
                $expr->in('p.targetId', ':groupIds'),
                $expr->eq('p.canViewFolder', ':canView')
            );
            $qb->setParameter('groupType', 'group')->setParameter('groupIds', $userGroups);
        }

        $qb->leftJoin('f.permissions', 'p')
            ->where($expr->orX($userPermission, $groupPermission))
            ->setParameter('userType', 'user')
            ->setParameter('userId', $user->getId())
            ->setParameter('canView', true)
            ->orderBy('f.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}