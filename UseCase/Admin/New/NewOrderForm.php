<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Orders\Order\UseCase\Admin\New;

use BaksDev\Orders\Order\UseCase\Admin\New\Invariable\NewOrderInvariableForm;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewOrderForm extends AbstractType
{
    public function __construct(private readonly UserProfileTokenStorageInterface $userProfileTokenStorage) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {
            /** @var NewOrderDTO $NewOrderDTO */
            $NewOrderDTO = $event->getData();

            /** Присваиваем ответственного пользователя */
            $NewOrderDTO->setProfile($this->userProfileTokenStorage->getProfile());

            /**
             * Новому заказу присваиваем только пользователя
             * Профиль заказа будет присвоен при упаковке
             */
            $NewOrderInvariableDTO = $NewOrderDTO->getInvariable();
            $NewOrderInvariableDTO->setUsr($this->userProfileTokenStorage->getUser());

        });


        $builder->add('preProduct', preProduct\PreProductForm::class, ['label' => false]);

        /* Коллекция продукции */
        $builder->add('product', CollectionType::class, [
            'entry_type' => Products\NewOrderProductForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__product__',
        ]);

        $builder->add('invariable', NewOrderInvariableForm::class, ['label' => false]);

        $builder->add('usr', User\OrderUserForm::class, ['label' => false]);

        $builder->add('comment', TextareaType::class, ['required' => false]);

        $builder->add(
            'order_new',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewOrderDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

}
