<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    const TYPE_CONTAINS = 1;
    const TYPE_EQUAL = 2;
    const TYPE_START_WITH = 3;
    const TYPE_END_WITH = 4;

    public function getBlockPrefix()
    {
        return 'sonata_dto_type_filter_choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'label_type_contains' => self::TYPE_CONTAINS,
            'label_type_equals' => self::TYPE_EQUAL,
            'label_type_start_with' => self::TYPE_START_WITH,
            'label_type_end_with' => self::TYPE_END_WITH,
        ];

        $builder
            ->add(
                'type',
                $options['operator_type'],
                array_merge([
                    'required' => false
                ], $options['operator_options'], [
                    'choice_translation_domain' => 'VtechSonataDTOAdminBundle',
                    'choices' => $choices,
                ])
            )
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_type' => FormChoiceType::class,
            'field_options' => [],
            'operator_type' => FormChoiceType::class,
            'operator_options' => [],
        ]);
    }
}
