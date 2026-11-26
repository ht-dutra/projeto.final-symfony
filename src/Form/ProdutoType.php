<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Produto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProdutoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', null, ['label' => 'Nome'])
            ->add('quantidade', null, ['label' => 'Quantidade'])
            ->add('descricao', null, ['label' => 'Descrição'])
            ->add('valor', null, ['label' => 'Valor'])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nome',
                'label' => 'Categoria',
            ])
            ->add('imagemUrl', null, ['label' => 'URL da Imagem', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produto::class,
        ]);
    }
}
