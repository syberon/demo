<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog\Factory\Controller;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MtModules\Catalog\Controller\FeedbackController;
use MtModules\Catalog\Form\Feedback;
use MtModules\Catalog\Model\Feedbacks;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class FeedbackControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null|array $options
     * @return FeedbackController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeedbackController
    {
        $form = new Feedback();
        $model = $container->get(Feedbacks::class);
        $form->setInputFilter($model->getInputFilter());
        return new FeedbackController($container->get(Feedbacks::class), $form);
    }
}