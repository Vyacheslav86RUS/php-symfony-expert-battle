<?php

namespace Site\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="Site\BackendBundle\Entity\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var \QuestionRound
     *
     * @ORM\ManyToOne(targetEntity="QuestionRound")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="question_round_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $questionRound;

    /**
     * @var \QuestionSubject
     *
     * @ORM\ManyToOne(targetEntity="QuestionSubject")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="question_subject_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $questionSubject;

    /**
     * @var PossibleAnswer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="PossibleAnswer", mappedBy="question")
     * @ORM\OrderBy({"sortorder" = "ASC"})
     */
    protected $possibleAnswers;

    /**
     * @var Answers[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Answers", mappedBy="question")
     */
    protected $answers;

    /**
     * @var integer
     *
     * @ORM\Column(name="for_multiplayer", type="boolean", nullable=false)
     */
    protected $forMultiplayer;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="question_day", type="boolean", nullable=false)
     */
    protected $questionDay;

    /**
     * @ORM\ManyToMany(targetEntity="Fight", mappedBy="questions")
     */
    private $fights;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Question
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Question
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return Question
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    
        return $this;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set questionRound
     *
     * @param \Site\BackendBundle\Entity\QuestionRound $questionRound
     * @return Question
     */
    public function setQuestionRound(\Site\BackendBundle\Entity\QuestionRound $questionRound = null)
    {
        $this->questionRound = $questionRound;
    
        return $this;
    }

    /**
     * Get questionRound
     *
     * @return \Site\BackendBundle\Entity\QuestionRound 
     */
    public function getQuestionRound()
    {
        return $this->questionRound;
    }

    /**
     * Set questionSubject
     *
     * @param \Site\BackendBundle\Entity\QuestionSubject $questionSubject
     * @return Question
     */
    public function setQuestionSubject(\Site\BackendBundle\Entity\QuestionSubject $questionSubject = null)
    {
        $this->questionSubject = $questionSubject;

        return $this;
    }

    /**
     * Get questionSubject
     *
     * @return \Site\BackendBundle\Entity\QuestionSubject
     */
    public function getQuestionSubject()
    {
        return $this->questionSubject;
    }

    /**
     * @return PossibleAnswer[]|ArrayCollection
     */
    public function getPossibleAnswers()
    {
        return $this->possibleAnswers;
    }

    /**
     * @param PossibleAnswer[] $possibleAnswers
     */
    public function setPossibleAnswers($possibleAnswers)
    {
        $this->possibleAnswers = $possibleAnswers;
    }

    /**
     * Add possibleAnswer
     *
     * @param PossibleAnswer $possibleAnswer
     * @return Question
     */
    public function addPossibleAnswer(PossibleAnswer $possibleAnswer)
    {
        $this->possibleAnswers[] = $possibleAnswer;

        return $this;
    }

    /**
     * Remove possibleAnswer
     *
     * @param PossibleAnswer $possibleAnswer
     */
    public function removePossibleAnswer(PossibleAnswer $possibleAnswer)
    {
        $this->possibleAnswers->removeElement($possibleAnswer);
    }

    /**
     * @param Answers[] $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * Add answer
     *
     * @param Answers $answer
     * @return Question
     */
    public function addAnswer(Answers $answer)
    {
        $this->answers[] = $answer;

        return $this;
    }

    /**
     * Remove answer
     *
     * @param Answers $answer
     */
    public function removeAnswer(Answers $answer)
    {
        $this->answers->removeElement($answer);
    }

    /**
     * Set forMultiplayer
     *
     * @param boolean $forMultiplayer
     * @return Question
     */
    public function setForMultiplayer($forMultiplayer)
    {
        $this->forMultiplayer = $forMultiplayer;

        return $this;
    }

    /**
     * Get forMultiplayer
     *
     * @return boolean
     */
    public function getForMultiplayer()
    {
        return $this->forMultiplayer;
    }
    
    /**
     * Set questionDay
     *
     * @param boolean $questionDay
     * @return Question
     */
    public function setQuestionDay($questionDay)
    {
        $this->questionDay = $questionDay;

        return $this;
    }

    /**
     * Get questionDay
     *
     * @return boolean
     */
    public function getQuestionDay()
    {
        return $this->questionDay;
    }
    

    public function __toString()
    {
        return ''.$this->id;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->possibleAnswers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fights = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add fights
     *
     * @param \Site\BackendBundle\Entity\Fight $fights
     * @return Question
     */
    public function addFight(\Site\BackendBundle\Entity\Fight $fights)
    {
        $this->fights[] = $fights;
    
        return $this;
    }

    /**
     * Remove fights
     *
     * @param \Site\BackendBundle\Entity\Fight $fights
     */
    public function removeFight(\Site\BackendBundle\Entity\Fight $fights)
    {
        $this->fights->removeElement($fights);
    }

    /**
     * Get fights
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFights()
    {
        return $this->fights;
    }
}