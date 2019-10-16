<?php

namespace Site\BackendBundle\Controller;

use Site\BackendBundle\Entity\Answers;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Site\BackendBundle\Entity\Fight;
use RMS\PushNotificationsBundle\Message\AndroidMessage;
use RMS\PushNotificationsBundle\Message\iOSMessage;

class ApiController extends Controller
{

    /**
     *  Проверка на наличие доступа
     *
     *  @var string $hash
     *  @var string $role
     *  @return bool
     **/
    private function getAccess($hash, $role = 'ROLE_SUPER_ADMIN')
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('salt' => $hash));
        if ($user && $user->hasRole($role)) {
            return true;
        }
        return false;
    }

    /**
     *  Добавление пользователя в базу
     *
     *  @return Response
     **/
    public function addUserAction()
    {
        $request = $this->getRequest()->request;
        $hash = $request->get('hash');
        $userManager = $this->get('fos_user.user_manager');
        $data['username'] = $request->get('username');
        $data['email'] = $request->get('email');
        $data['password'] = $request->get('password');
        $data['post'] = $request->get('post');
        $data['shop'] = $request->get('shop');
        $data['id_user'] = $request->get('id_user');
        $data['name'] = $request->get('name');
        $data['surname'] = $request->get('surname');
        $data['middle_name'] = $request->get('middle_name');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($hash, 'ROLE_SUPER_ADMIN')) {
            $isNull = false; // все параметры переданы
            foreach ($data as $key => $item) {
                if (!$item) {
                    $isNull = $key; // пустой параметр
                    break;
                }
            }
            if ($isNull === false) {
                $flag = 0; // пользователь существует в базе
                $userById = $userManager->findUserBy(array('idUser' => $data['id_user']));
                $userByUsername = $userManager->findUserByEmail($data['email']);
                $userByEmail = $userManager->findUserByUsername($data['username']);
                if (!$userById) {
                    if (!$userByUsername && !$userByEmail) {
                        $flag = 1; // пользователь новый
                        $user = $userManager->createUser();
                    } else {
                        return new Response(json_encode(array('RESULT' => '-1', 'ERROR' => '500', 'DESCRIPTION' => 'Username or/and email is already exist!')));
                    }
                } else {
                    if (!$userByUsername && ($userById == $userByEmail) || !$userByEmail && ($userById == $userByUsername || !$userByUsername && !$userByEmail)) {
                        $user = $userManager->findUserBy(array('idUser' => $data['id_user']));
                    } else {
                        return new Response(json_encode(array('RESULT' => '-1', 'ERROR' => '500', 'DESCRIPTION' => 'Username or/and email is already exist!')));
                    }
                }
                $user->setUsername($data['username']);
                $user->setEmail($data['email']);
                $user->setPassword($data['password']);
                $user->setIdUser($data['id_user']);
                $user->setPost($data['post']);
                $user->setShop($data['shop']);
                $user->setName($data['name']);
                $user->setSurname($data['surname']);
                $user->setMiddleName($data['middle_name']);
                $user->setEnabled(1);
                $userManager->updateUser($user);
                $this->getDoctrine()->getManager()->flush();
                if ($flag) {
                    $response->setContent(json_encode(array('RESULT' => '0', 'ERROR' => '0')));
                    return $response;
                }
                $response->setContent(json_encode(array('RESULT' => '1', 'ERROR' => '0')));
                return $response;
            }
            $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '500', 'DESCRIPTION' => 'Parameter '. $isNull .' must not be null!')));
            return $response;
        }
        $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '403', 'DESCRIPTION' => 'Access denied!')));
        return $response;
    }

    /**
     *  Деактивация пользователя
     *
     *  @return Response
     **/
    public function deactivateUserAction()
    {
        $request = $this->getRequest()->request;
        $hash = $request->get('hash');
        $idUser = $request->get('id_user');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($hash, 'ROLE_SUPER_ADMIN')) {
            if ($idUser) {
                $user = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('idUser' => $idUser));
                if ($user && !$user->hasRole('ROLE_SUPER_ADMIN')) {
                    $user->setEnabled(0);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $response->setContent(json_encode(array('RESULT' => '0', 'ERROR' => '0')));
                    return $response;
                }
                $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '404', 'User with id_user='.$idUser.' does not exist!')));
                return $response;
            }
            $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '500', 'DESCRIPTION' => 'Parameter id_user must not be null!')));
            return $response;
        }
        $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '403', 'DESCRIPTION' => 'Access denied!')));
        return $response;
    }

    /**
     *  Активация пользователя
     *
     *  @return Response
     **/
    public function activateUserAction()
    {
        $request = $this->getRequest()->request;
        $hash = $request->get('hash');
        $idUser = $request->get('id_user');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($hash, 'ROLE_SUPER_ADMIN')) {
            if ($idUser) {
                $user = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('idUser' => $idUser));
                if ($user && !$user->hasRole('ROLE_SUPER_ADMIN')) {
                    $user->setEnabled(1);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $response->setContent(json_encode(array('RESULT' => '0', 'ERROR' => '0')));
                    return $response;
                }
                $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '404', 'User with id_user='.$idUser.' does not exist!')));
                return $response;
            }
            $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '500', 'DESCRIPTION' => 'Parameter id_user must not be null!')));
            return $response;
        }
        $response->setContent(json_encode(array('RESULT' => '-1', 'ERROR' => '403', 'DESCRIPTION' => 'Access denied!')));
        return $response;
    }

    /**
     *  Авторизация пользователя
     *
     *  @return Response
     **/
    public function authAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $request = $this->getRequest()->request;
        $em = $this->getDoctrine()->getManager();
        $username = $request->get('username');
        $password = $request->get('password');
        $apns = $request->get('apns_token');
        $gcm = $request->get('gcm_token');
        $user = $userManager->findUserByUsername($username);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($user->getPassword() === $password && $user->isEnabled()) {
            if ($apns) {
                $user->setApnsToken($apns);
            }
            if ($gcm) {
                $user->setGcmToken($gcm);
            }
            $em->persist($user);
            $em->flush();
            $response->setContent(json_encode(array('success', $user->getSalt())));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Получение всех тем
     *
     *  @return Response
    **/
    public function getAllSubjectsAction()
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $subjects = $this->getDoctrine()->getRepository('SiteBackendBundle:QuestionSubject')->getAllAsArray();
            $response->setContent(json_encode(array('success', $subjects)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Получение всех раундов одной темы
     *
     *  @var integer $id
     *  @return Response
     **/
    public function getAllRoundsOfSubjectAction($id)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $userId = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('salt' => $request->get('hash')))->getId();
            $rounds = $this->getDoctrine()->getRepository('SiteBackendBundle:QuestionRound')->getAllRoundsOfSubjectAsArray($id);
            $flag = true;
            foreach ($rounds as $key => &$round) {
                $round['questions'] = count($round['questions']);
                $count = 0;
                foreach($round['answers'] as $answer) {
                    if ($answer['isTrue'] == 1 && $answer['user']['id'] == $userId) {
                        $count++;
                    }
                }
                $round['answers'] = $count;
                $round['passed'] = false;
                $round['available'] = false;
                if ($key == 0 || ($key > 0 && $rounds[$key - 1]['passed'] == true)) {
                    $round['available'] = true;
                }
                if ($count > 0 && $count >= $round['desiredAmount']) {
                    $round['passed'] = true;
                }
            }
            $response->setContent(json_encode(array('success', $rounds)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
    *  Получение всех вопросов в игре "Битва дня"
    *
    *  
    *  @return Response
    **/
    public function getAllQuestionsBattleOfTheDayAction()
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserBy(array('salt' => $request->get('hash')));
            $questions = $this->getDoctrine()->getRepository('SiteBackendBundle:Question')->getAllQuestionBattleOfTheDayAsArray($user);
            $this->wrapUpAnswersForQuestions($questions);
            $response->setContent(json_encode(array('success', $questions)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }
    
    /**
     *  Получение всех вопросов одного теста вместе с возможными ответами
     *
     *  @var integer $id
     *  @return Response
     **/
    public function getAllQuestionsWithPossiblesAnswersAction($id)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserBy(array('salt' => $request->get('hash')));
            $questions = $this->getDoctrine()->getRepository('SiteBackendBundle:Question')->getAllQuestionsWithAnswersOfRoundAsArray($id, $user);
            $this->wrapUpAnswersForQuestions($questions);
            $response->setContent(json_encode(array('success', $questions)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    // упаковка вариантов ответов и ответов пользователей
    private function wrapUpAnswersForQuestions(&$questions, $fightId = null)
    {
        foreach($questions as &$question) {
            $answered = null;
            foreach($question['answers'] as $answer) {
                if ( isset($question['fights']) && in_array(['id' => $fightId], $question['fights'])) {
                    if ($answer['fights'] && $answer['isTrue'] && in_array(['id' => $fightId], $answer['fights'])) {
                        $answered = true;
                        break;
                    } elseif ($answer['fights'] && in_array(['id' => $fightId], $answer['fights']) && !$answered && !$answer['isTrue']) {
                        $answered = false;
                    }
                } else {
                    if ($answer['isTrue']) {
                        $answered = true;
                        break;
                    } elseif (!$answered && !$answer['isTrue']) {
                        $answered = false;
                    }
                }
            }
            if (isset($question['fights'])) {
                unset($question['fights']);
            }
            $question['answered'] = $answered;
            unset($question['answers']);
            switch ($question['type']) {
                case 1:
                case 2:
                    foreach($question['possibleAnswers'] as $key => $possibleAnswer) {
                        $question['variants'][$key] = array(
                            'answer' => $possibleAnswer['answer1'],
                            'image' => $possibleAnswer['image1'],
                            'isTrue' => $possibleAnswer['isTrue']
                        );
                    }
                    break;
                case 3 :
                    foreach($question['possibleAnswers'] as $key => $possibleAnswer) {
                        $question['variants'][$key][0] = array(
                            'answer' => $possibleAnswer['answer1'],
                            'image' => $possibleAnswer['image1'],
                            'id' => $possibleAnswer['id']
                        );
                        $question['variants'][$key][1] = array(
                            'answer' => $possibleAnswer['answer2'],
                            'image' => $possibleAnswer['image2'],
                            'id' => $possibleAnswer['id']
                        );
                    }
                    break;
                case 4 :
                case 6 :
                    foreach($question['possibleAnswers'] as $key => $possibleAnswer) {
                        $question['variants'][$key] = array(
                            'answer' => $possibleAnswer['answer1'],
                        );
                    }
                    break;
                case 5 :
                    foreach($question['possibleAnswers'] as $key => $possibleAnswer) {
                        $question['variants'][$key] = array(
                            'answer' => $possibleAnswer['answer1'],
                            'image' => $possibleAnswer['image1'],
                            'sortorder' => $possibleAnswer['sortorder']
                        );
                    }
                    break;
            }
            unset($question['possibleAnswers']);
        }
    }

    /**
     *  Отправка ответа на вопрос
     *
     *  @var integer $id
     *  @return Response
     **/
    public function setAnswerAction($id)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $fight = $this->getDoctrine()->getRepository('SiteBackendBundle:Fight')->find(+$request->get('fightId'));
            $question = $this->getDoctrine()->getRepository('SiteBackendBundle:Question')->find(+$id);
            if (!$question) {
                return new Response(json_encode(array('failed', 'Question with id='.$id.' does not exist!')));
            }
            $isTrue = $request->get('is_true') ? $request->get('is_true') : 0;
            $user = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('salt' => $request->get('hash')));
            $dbAnswers = $this->getDoctrine()->getRepository('SiteBackendBundle:Answers')->findBy(array('question' => +$id, 'user' => $user->getId()));
            $answer = new Answers();
            $rating = $user->getRating();
            $isCredited = false;
            foreach($dbAnswers as $dbAnswer) {
                if ($dbAnswer->getCredited()) {
                    $isCredited = true;
                    break;
                }
            }
            if ($isTrue && !$isCredited) {
                $rating += $question->getWeight();
                $user->setRating($rating);
                $answer->setCredited(1);
            }
            $answer->setQuestion($question);
            $answer->setUser($user);
            if (!$answer->getIsTrue()) {
                $answer->setIsTrue($isTrue);
            }
            $answer->setQuestionRound($question->getQuestionRound());
            $em = $this->getDoctrine()->getManager();
            if ($request->get('fightId')) {
                $fight->addAnswer($answer);
                $em->persist($fight);
            }
            $em->persist($answer);
            $em->persist($user);
            $em->flush();

            $response->setContent(json_encode(array('success')));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения случайного соперника
     *
     *  @return Response
     **/
    public function getRandomRivalAction()
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $resUser = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->getRandRival($request->get('hash'));
            if (!empty($resUser)) {
                $resArray = [
                    'id' => $resUser[0]->getId(),
                    'post' => $resUser[0]->getPost(),
                    'name' => $resUser[0]->getName(),
                    'surname' => $resUser[0]->getSurname(),
                    'middleName' => $resUser[0]->getMiddleName(),
                    'rating' => $resUser[0]->getRating()
                ];
                $response->setContent(json_encode(array('success', $resArray)));
                return $response;
            }
            $response->setContent(json_encode(array('failed', 'User not found!')));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения всех активных пользователей
     *
     *  @return Response
     **/
    public function getAllUsersAction()
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $users = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->getAllActiveUsers();
            $response->setContent(json_encode(array('success', $users)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод вызова соперника
     *
     *  @var integer $userId
     *  @return Response
     **/
    public function challengeAction($userId)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $em = $this->getDoctrine()->getManager();
            $firstUser = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('salt' => $request->get('hash')));
            $secondUser = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->find($userId);
            if ($firstUser == $secondUser) {
                $response->setContent(json_encode(array('failed', 'You can\'t challenge yourself!')));
                return $response;
            }
            $fight = new Fight();
            $fight->setFirstUser($firstUser);
            $fight->setSecondUser($secondUser);
            $fight->setStatus(0);
            $fight->setCreatedAt(new \DateTime());
            $questions = $this->getDoctrine()->getRepository('SiteBackendBundle:Question')->getQuestionsForFight();
            $fight->setQuestions($questions);
            $em->persist($fight);
            $em->flush();

            if ($secondUser->getApnsToken()) {
                $message = new iOSMessage();
                $message->setDeviceIdentifier($secondUser->getApnsToken());
                $message->setAPSSound('default');
                $message->setMessage('Вам бросили вызов!');
                $message->setAPSBadge(1);
                $message->setAPSContentAvailable('Игрок '. $firstUser->getName() . ' ' . $firstUser->getMiddleName() . ' бросил Вам вызов!');
                $message->addCustomData('id', $fight->getId());
            } elseif ($secondUser->getGcmToken()) {
                $message = new AndroidMessage();
                $message->setGCM(true);
                $message->setMessage('Игрок '. $firstUser->getName() . ' ' . $firstUser->getMiddleName() . ' бросил Вам вызов!');
                $message->setData(['id' => $fight->getId()]);
                $message->setDeviceIdentifier($secondUser->getGcmToken());
            } else {
                $response->setContent(json_encode(array('failed', "User has no token!")));
                return $response;
            }

            $this->container->get('rms_push_notifications')->send($message);

            $response->setContent(json_encode(array('success', $fight->getId())));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения статуса вызова
     *
     *  @var integer $fightId
     *  @return Response
     **/
    public function getFightStatusAction($fightId)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $fight = $this->getDoctrine()->getRepository('SiteBackendBundle:Fight')->find($fightId);
            $response->setContent(json_encode(array('success', $fight->getStatus())));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения списка вопросов для боя
     *
     *  @var integer $fightId
     *  @return Response
     **/
    public function getQuestionsListForFightAction($fightId)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $fight = $this->getDoctrine()->getRepository('SiteBackendBundle:Fight')->find($fightId);
            if (!$fight) {
                $response->setContent(json_encode(array('failed', 'Fight with fightId = ' . $fightId . ' doesn\'t exist!')));
                return $response;
            }
            $user = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('salt' => $request->get('hash')));
            $questions = $this->getDoctrine()->getRepository('SiteBackendBundle:Question')->getAllQuestionsWithAnswersForFightAsArray($fightId, $user->getId());
            $this->wrapUpAnswersForQuestions($questions, $fightId);
            $response->setContent(json_encode(array('success', $questions)));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения результатов боя
     *
     *  @var integer $fightId
     *  @return Response
     **/
    public function getFightResultsAction($fightId)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $currentUser = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->findOneBy(array('salt' => $request->get('hash')));
            $fight = $this->getDoctrine()->getRepository('SiteBackendBundle:Fight')->getOneWithAnswers($fightId);
            $results = [$fight->getFirstUser()->getId() => 0, $fight->getSecondUser()->getId() => 0];
            $usersAnswers = [];
            foreach($fight->getAnswers() as $answer) {
                if (!isset($usersAnswers[$answer->getUser()->getId()][$answer->getId()])) {
                    $usersAnswers[$answer->getUser()->getId()][$answer->getId()] = $answer->getIsTrue();
                } else {
                    $usersAnswers[$answer->getUser()->getId()][$answer->getId()] += $answer->getIsTrue();
                }
            }
            if (isset($usersAnswers[$fight->getFirstUser()->getId()]) && isset($usersAnswers[$fight->getSecondUser()->getId()]) &&
                (count($usersAnswers[$fight->getFirstUser()->getId()]) == count($usersAnswers[$fight->getSecondUser()->getId()]) &&
                count($usersAnswers[$fight->getSecondUser()->getId()]) == count($fight->getQuestions()))) {
                foreach($usersAnswers[$fight->getFirstUser()->getId()] as $item) {
                    if ($item) {
                        $results[$fight->getFirstUser()->getId()] ++;
                    }
                }
                foreach($usersAnswers[$fight->getSecondUser()->getId()] as $item) {
                    if ($item) {
                        $results[$fight->getSecondUser()->getId()] ++;
                    }
                }
                $results['currentUserId'] = $currentUser->getId();
                $response->setContent(json_encode(array('success', $results)));
                return $response;
            }

            $response->setContent(json_encode(array('failed', 'The fight is not finished!')));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Ответ на запрос
     *
     *  @var integer $fightId
     *  @return Response
     **/
    public function challengeResponseAction($fightId)
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $resp = $request->get('response');
            $em = $this->getDoctrine()->getManager();
            $fight = $this->getDoctrine()->getRepository('SiteBackendBundle:Fight')->find($fightId);
            $fight->setStatus($resp);
            $em->persist($fight);
            $em->flush();

            $firstUser = $fight->getFirstUser();
            $secondUser = $fight->getSecondUser();

            $answer = $resp == 1 ? 'Пользователь принял Ваш вызов!' : 'Пользоватеьл отклонил Ваш вызов!';
            $answerMessage = $resp == 1 ? 'Пользователь ' . $secondUser->getName() . ' ' . $secondUser->getMiddleName() . ' принял Ваш вызов!' :
                'Пользователь ' . $secondUser->getName() . ' ' . $secondUser->getMiddleName() . ' отклонил Ваш вызов!';
            if ($firstUser->getApnsToken()) {
                $message = new iOSMessage();
                $message->setDeviceIdentifier($firstUser->getApnsToken());
                $message->setAPSSound('default');
                $message->setMessage($answer);
                $message->setAPSBadge(1);
                $message->setAPSContentAvailable($answerMessage);
                $message->addCustomData('id', $fight->getId());
            } elseif ($firstUser->getGcmToken()) {
                $message = new AndroidMessage();
                $message->setGCM(true);
                $message->setMessage($answerMessage);
                $message->setData(['id' => $fight->getId()]);
                $message->setDeviceIdentifier($firstUser->getGcmToken());
            } else {
                $response->setContent(json_encode(array('failed', "User has no token!")));
                return $response;
            }
            $message->setMessage($answer);
            $this->container->get('rms_push_notifications')->send($message);

            $response->setContent(json_encode(array('success')));
            return $response;
        }
        $response->setContent(json_encode(array('failed', 'Access denied!')));
        return $response;
    }

    /**
     *  Метод получения всех магазинов
     *
     *  @return Response
     **/
    public function getAllShopAction()
    {
        $request = $this->getRequest()->request;
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        if ($this->getAccess($request->get('hash'), 'ROLE_USER')) {
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserBy(array('salt' => $request->get('hash')));
            $user_shop = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->getShopUser($user->getId());
            if($user_shop[0]['shop'] == ''){
                $response->setContent(json_encode(array('error'=>'user not shop')));
                return $response;
            }
            $count_shop = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->checkCountUserShop($user_shop[0]['shop']);
            if(intval($count_shop[0][1]) <= 1){
                $response->setContent(json_encode(array('error'=>'shop has few users')));
                return $response;
            }
            $shop = $this->getDoctrine()->getRepository('SiteBackendBundle:User')->getAllShops($user_shop[0]['shop']);
            if(empty($shop)){
                $response->setContent(json_encode(array('failed', 'Not found shop')));
                return $response;
            }
              $response->setContent(json_encode(array('shop', $shop)));
              return $response;
        }
        $response->setContent(json_encode(array(
                )));
        return $response;
        
   
    }
}
