<?php
/**
 * Copyright (c) 2018.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Cdek;

use Laminas\Router\Http\Literal;

return [

    'mt-cdek' => [
        'cities-url'       => 'https://integration.cdek.ru/v1/location/cities/json?countryCode=RU',
        'autocomplete-url' => 'https://api.cdek.ru/city/getListByTerm/json.php'
    ],

    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Factory\Controller\IndexControllerFactory::class
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ]
    ],

    'router' => [
        'routes' => [
            'cdek' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/admin/cdek',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                    ]
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'config'           => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/config',
                            'defaults' => [
                                'action' => 'config'
                            ]
                        ]
                    ],
                    'calculate'        => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/calculate',
                            'defaults' => [
                                'action' => 'calculate'
                            ]
                        ]
                    ],
                    'find-city'        => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/find-city',
                            'defaults' => [
                                'action' => 'find-city'
                            ]
                        ]
                    ],
                    'get-autocomplete' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/get-autocomplete',
                            'defaults' => [
                                'action' => 'get-autocomplete'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
