<?php
namespace Site\BackendBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

class QuestionRepository extends EntityRepository
{
    public function getAllQuestionsWithAnswersOfRoundAsArray($id, $user)
    {
        return $this->createQueryBuilder('q')
            ->select('q, pa, a')
            ->where('q.questionRound = :id')
            ->setParameter('id', $id)
            ->leftJoin('q.possibleAnswers', 'pa')
            ->leftJoin('q.answers', 'a', 'WITH', 'a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pa.sortorder')
            ->getQuery()
            ->getArrayResult();
    }
    
    public function getAllQuestionBattleOfTheDayAsArray($user)
    {
        return $this->createQueryBuilder('q')
            ->select('q, pa, a')
            ->where('q.questionDay = :questionDay')
            ->setParameter('questionDay', 1)
            ->leftJoin('q.possibleAnswers', 'pa')
            ->leftJoin('q.answers', 'a', 'WITH', 'a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pa.sortorder')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAllQuestionsWithAnswersForFightAsArray($fightId, $userId)
    {
        return $this->createQueryBuilder('q')
            ->select('q, pa, a, partial f.{id}, partial af.{id}')
            ->where('f.id = :fightId')
            ->setParameter('fightId', $fightId)
            ->leftJoin('q.possibleAnswers', 'pa')
            ->innerJoin('q.fights', 'f')
            ->leftJoin('q.answers', 'a', 'WITH', 'a.user = :user')
            ->leftJoin('a.fights', 'af')
            ->setParameter('user', $userId)
            ->orderBy('pa.sortorder')
            ->getQuery()
            ->getArrayResult();
    }

    public function getQuestionsForFight()
    {
        return $this->createQueryBuilder('q')
            ->where('q.forMultiplayer = :forMultiplayer')
            ->setParameter('forMultiplayer', 1)
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(15)
            ->getQuery()
            ->execute();
    }

    public function findFreeQuestions()
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.questionSubject', 'qs')
            ->where('q.questionRound is NULL')
            ->getQuery()
            ->execute();
    }

    public function findAllByIds($ids)
    {
        return $this->createQueryBuilder('q')
            ->where('q.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}