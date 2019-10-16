<?php
namespace Site\BackendBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

class UserRepository extends EntityRepository
{
    public function getEmailByUserId($id)
    {
        $params = array(
            new Parameter('id', $id),
        );

        return $this->createQueryBuilder('u')
            ->select('u.email')
            ->where('u.id = :id')
            ->setParameters(new ArrayCollection($params))
            ->getQuery()
            ->getOneOrNullResult(2);
    }

    public function getRandRival($hash)
    {
        return $this->createQueryBuilder('u')
            ->where('u.hash != :hash')
            ->andWhere('u.roles NOT LIKE :role')
            ->andWhere('u.enabled = :status')
            ->setParameter('hash', $hash)
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->setParameter('status', 1)
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->execute();

    }

    public function getAllActiveUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('partial u.{id, email, name, surname, middleName, rating, post, shop}')
            ->where('u.enabled = :status')
            ->andWhere('u.roles NOT LIKE :role')
            ->setParameter('status', 1)
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->getArrayResult();
    }
    
    public function getAllShops($name_shop)
    {
        $ptm = $this->createQueryBuilder('u')
                ->select('COUNT(u) as quantity,u.shop')
                ->where('u.shop <> :name AND (u.apnsToken <> :apns OR u.gcmToken <> :gcm) AND u.shop <> :name_shop')
                ->setParameter('name', '')
                ->setParameter('apns', '')
                ->setParameter('gcm', '')
                ->setParameter('name_shop', $name_shop)
                ->groupBy('u.shop')
                ->getQuery()
                ->getArrayResult();
                $result = array();
                foreach ($ptm as $key => $value) {
                  if(intval($value['quantity']) > 1){
                    array_push($result, $value['shop']);
                  }
                }
                return $result;
    }
    
    public function getShopUser($Uid){
        return $this->createQueryBuilder('u')
                ->select('u.shop')
                ->where('u.id = :user_id')
                ->setParameter('user_id', $Uid)
                ->getQuery()
                ->getArrayResult();
    }
    
    public function checkCountUserShop($name_shop)
    {
        return $this->createQueryBuilder('u')
                ->select('COUNT(u)')
                ->where('u.shop = :name')
                ->setParameter('name', $name_shop)
                ->getQuery()
                ->getArrayResult();
    }
}