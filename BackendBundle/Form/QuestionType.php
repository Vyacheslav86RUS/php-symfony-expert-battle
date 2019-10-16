<?php

namespace Site\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
{
        $builder
           ->add('text','textarea', array(
               'label' => 'Текст вопроса',
//               'attr' => array(
//               'class' => 'tinymce',
//               'data-theme' => 'medium' // simple, advanced, bbcode
//               )
             ))
           ->add('type', 'choice', array(
               'label' => 'Тип ответа',
               'choices' => array(
                   '1' => 'Одиночный ответ',
                   '2' => 'Множественный ответ',
                   '3' => 'На соответствие',
                   '4' => 'Числовой',
                   '5' => 'Последовательный',
                   '6' => 'Свободный ответ'
               )
           ))
           ->add('weight', 'text', array(
               'label' => 'Вес вопроса'
           ))
            ->add('forMultiplayer', null, array(
                'label' => 'Возможность использоваать для мультиплеера',
                'attr' => array('style' => 'width: auto;')
            ))
            ->add('questionDay',null,array(
                'label' => 'Добавить вопрос в битву дня',
                'attr' => array('style' => 'width: auto;')
            ))
            ->add('questionRound', 'entity', array(
                'label' => 'Раунд (выставляется автоматически)',
                'class' => 'Site\BackendBundle\Entity\QuestionRound',
                'required' => false,
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
{
        $resolver->setDefaults(array(
            'data_class' => 'Site\BackendBundle\Entity\Question'
        ));
    }

    public function getName()
{
    return 'site_backendbundle_questiontype';
}
}
