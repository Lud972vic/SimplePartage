<?php

namespace App\Repository;

use App\Entity\Folder;
use App\Entity\FolderPermission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FolderPermission>
 *
 * @method FolderPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method FolderPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method FolderPermission[]    findAll()
 * @method FolderPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderPermissionRepository extends ServiceEntityRepository
{
    /**
     * FolderPermissionRepository constructor.
     *
     * @param ManagerRegistry $registry The manager registry.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FolderPermission::class);
    }

    /**
     * Persists a FolderPermission entity to the database.
     *
     * @param FolderPermission $entity The FolderPermission entity to save.
     * @param bool $flush Whether to flush the entity manager after persisting.
     */
    public function save(FolderPermission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes a FolderPermission entity from the database.
     *
     * @param FolderPermission $entity The FolderPermission entity to remove.
     * @param bool $flush Whether to flush the entity manager after removing.
     */
    public function remove(FolderPermission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Finds the specific permission for a user on a given folder.
     *
     * @param Folder $folder The folder to check permissions for.
     * @param User $user The user to check permissions for.
     * @return FolderPermission|null The permission object if found, otherwise null.
     */
    public function findUserPermissions(Folder $folder, User $user): ?FolderPermission
    {
        return $this->createQueryBuilder('p')
            ->where('p.folder = :folder')
            ->andWhere('p.targetType = :targetType')
            ->andWhere('p.targetId = :targetId')
            ->setParameter('folder', $folder)
            ->setParameter('targetType', FolderPermission::TARGET_USER)
            ->setParameter('targetId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds all permissions for a set of groups on a given folder.
     *
     * @param Folder $folder The folder to check permissions for.
     * @param array $groupIds An array of group IDs to check permissions for.
     * @return FolderPermission[] An array of FolderPermission objects.
     */
    public function findGroupPermissions(Folder $folder, array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }
        
        return $this->createQueryBuilder('p')
            ->where('p.folder = :folder')
            ->andWhere('p.targetType = :targetType')
            ->andWhere('p.targetId IN (:groupIds)')
            ->setParameter('folder', $folder)
            ->setParameter('targetType', FolderPermission::TARGET_GROUP)
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Counts the number of permissions associated with a specific folder.
     *
     * @param Folder $folder The folder to count permissions for.
     * @return int The total number of permissions.
     */
    public function countByFolder(Folder $folder): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.folder = :folder')
            ->setParameter('folder', $folder)
            ->getQuery()
            ->getSingleScalarResult();
    }
}