<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 *
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    /**
     * FileRepository constructor.
     *
     * @param ManagerRegistry $registry The manager registry.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * Persists a File entity to the database.
     *
     * @param File $entity The File entity to save.
     * @param bool $flush Whether to flush the entity manager after persisting.
     */
    public function save(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes a File entity from the database.
     *
     * @param File $entity The File entity to remove.
     * @param bool $flush Whether to flush the entity manager after removing.
     */
    public function remove(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Finds all files within a specific folder.
     *
     * @param Folder $folder The folder to search in.
     * @return File[] An array of File objects.
     */
    public function findByFolder(Folder $folder): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.folder = :folder')
            ->setParameter('folder', $folder)
            ->orderBy('f.filename', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all files in a given folder that are accessible to a specific user.
     *
     * This method checks for permissions granted directly to the user or to any groups
     * the user is a member of.
     *
     * @param Folder $folder The folder to search in.
     * @param User $user The user for whom to check permissions.
     * @return File[] An array of accessible File objects.
     */
    public function findAccessibleFiles(Folder $folder, User $user): array
    {
        $userGroups = $user->getGroups()->map(fn($group) => $group->getId())->toArray();

        $qb = $this->createQueryBuilder('f');
        $expr = $qb->expr();

        $userPermission = $expr->andX(
            $expr->eq('p.targetType', ':userType'),
            $expr->eq('p.targetId', ':userId'),
            $expr->eq('p.canViewFiles', ':canView')
        );

        $groupPermission = '1=0'; // Default to a condition that is always false
        if (!empty($userGroups)) {
            $groupPermission = $expr->andX(
                $expr->eq('p.targetType', ':groupType'),
                $expr->in('p.targetId', ':groupIds'),
                $expr->eq('p.canViewFiles', ':canView')
            );
            $qb->setParameter('groupType', 'group')->setParameter('groupIds', $userGroups);
        }

        $qb->leftJoin('f.folder', 'folder')
            ->leftJoin('folder.permissions', 'p')
            ->where($expr->eq('f.folder', ':folder'))
            ->andWhere($expr->orX($userPermission, $groupPermission))
            ->setParameter('folder', $folder)
            ->setParameter('userType', 'user')
            ->setParameter('userId', $user->getId())
            ->setParameter('canView', true)
            ->orderBy('f.filename', 'ASC');

        return $qb->getQuery()->getResult();
    }
}