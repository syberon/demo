<?php
/**
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

namespace MtModules\Catalog;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use MtCms\User\Form\User;

return [

    'htimg' => [
        'filters' => [
            'catalog-category'        => [
                'type'    => 'thumbnail',
                'options' => [
                    'width'  => 370,
                    'height' => 245,
                    'format' => 'jpeg'
                ]
            ],
            'catalog-item-list'       => [
                'type'    => 'thumbnail',
                'options' => [
                    'width'  => 350,
                    'height' => 500,
                    'format' => 'jpg'
                ]
            ],
            'catalog-item-main'       => [
                'type'    => 'thumbnail',
                'options' => [
                    'width'  => 700,
                    'height' => 1000,
                    'format' => 'jpg'
                ]
            ],
            'catalog-item-additional' => [
                'type'    => 'thumbnail',
                'options' => [
                    'width'  => 400,
                    'height' => 400,
                    'format' => 'jpg'
                ]
            ],
            'catalog-item-big'        => [
                'type'    => 'relativeresize',
                'options' => [
                    'widen' => 1600
                ]
            ],
            'catalog-items-edit'      => [
                'type'    => 'thumbnail',
                'options' => [
                    'width'  => 185,
                    'height' => 185,
                    'format' => 'jpg'
                ]
            ]
        ]
    ],

    'catalog' => [
        'order_subject' => 'Оформлен новый заказ',

        // Параметры платежной системы
        'payment'       => [
            'login'     => '*',
            'password1' => '*',
            'password2' => '*',
            'payUrl'    => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'payTest'   => true
        ],

        'options' => [
            // Использование валюты
            'use-currency'         => false,

            // Использование торговых предложений
            'use-offers'           => true,

            // Использование бызы наличия товара
            'use-stock'            => true,

            // Отзывы на товары
            'use-feedback'         => true,

            // Привязка сопутствующих товаров
            'use-related'          => true,

            // Использование групп товаров
            'use-groups'           => true,

            // Использование внешней базы брендов
            'use-brands'           => false,

            // Показ товаров отсутствующих в наличии
            'display-out-of-stock' => false,

            // Использование скидки на сумму в корзине
            'use-cart-discount'    => false,

            // Использование промо-кодов
            'use-cart-coupons'     => true,

            // Использование поля с кратким описанием
            'items-use-preview'    => false,

            // Использование поля с артикулом товара
            'items-use-article'    => true,

            // Опции работы с корзиной
            'cart'                 => [
                // Обновление количества товара в корзине, если он уже добавлен ранее
                'update_exist' => true
            ],

            // Параметры синхронизации товаров с 1С
            'sync'                 => [
                /**
                 * Что делать с товарами, отсутствующими в выгрузке:
                 *
                 * deactivate - деактивировать
                 * keep       - оставлять не измененными
                 * delete     - удалять
                 */
                'not-exist-action'             => 'delete',

                // Переименование обработанного файла
                'rename-processed-file'        => false,

                // Допускать товары с незаполненными свойствами
                'allow-empty-items-properties' => false,

                // Удалять тороговые предложения без заданных свойств
                'delete-empty-offers'          => false,

                // Автоматическая загрузка категорий из файла выгрузки
                'parse-categories'             => true
            ],
        ],

        'pagination' => [
            'categories'  => 12,
            'items'       => 20,
            'items.admin' => 50,
        ],

        'filters' => [
            'category'   => 'catalog-category',
            'item-small' => 'catalog-item-small',
            'item-big'   => 'catalog-item-big',
            'item-add'   => 'catalog-item-additional'
        ]
    ],

    'controllers' => [
        'factories' => [
            Controller\CategoriesController::class               => Factory\Controller\CategoriesControllerFactory::class,
            Controller\CurrenciesController::class               => Factory\Controller\CurrenciesControllerFactory::class,
            Controller\ItemsPropertiesController::class          => Factory\Controller\ItemsPropertiesControllerFactory::class,
            Controller\ItemsPropertiesDefaultsController::class  => Factory\Controller\ItemsPropertiesDefaultsControllerFactory::class,
            Controller\OffersPropertiesController::class         => Factory\Controller\OffersPropertiesControllerFactory::class,
            Controller\OffersPropertiesDefaultsController::class => Factory\Controller\OffersPropertiesDefaultsControllerFactory::class,
            Controller\GroupsController::class                   => Factory\Controller\GroupsControllerFactory::class,
            Controller\ItemsController::class                    => Factory\Controller\ItemsControllerFactory::class,
            Controller\OffersController::class                   => Factory\Controller\OffersControllerFactory::class,
            Controller\PicturesController::class                 => Factory\Controller\PicturesControllerFactory::class,
            Controller\CartController::class                     => Factory\Controller\CartControllerFactory::class,
            Controller\OrdersController::class                   => Factory\Controller\OrdersControllerFactory::class,
            Controller\FavoritesController::class                => Factory\Controller\FavoritesControllerFactory::class,
            Controller\DiscountController::class                 => Factory\Controller\DiscountControllerFactory::class,
            Controller\CouponsController::class                  => Factory\Controller\CouponsControllerFactory::class,
            Controller\FeedbackController::class                 => Factory\Controller\FeedbackControllerFactory::class
        ]
    ],

    'service_manager' => [
        'factories' => [
            User::class                           => Factory\Form\UserFactory::class,
            Form\Item::class                      => Factory\Form\ItemFactory::class,
            Form\Offer::class                     => Factory\Form\OfferFactory::class,
            Model\Categories::class               => Factory\Model\CategoriesFactory::class,
            Model\Currencies::class               => Factory\Model\CurrenciesFactory::class,
            Model\ItemsProperties::class          => Factory\Model\ItemsPropertiesFactory::class,
            Model\ItemsCategoryLinker::class      => Factory\Model\ItemsCategoryLinkerFactory::class,
            Model\ItemsPropertiesValues::class    => Factory\Model\ItemsPropertiesValuesFactory::class,
            Model\ItemsPropertiesDefaults::class  => Factory\Model\ItemsPropertiesDefaultsFactory::class,
            Model\Pictures::class                 => Factory\Model\PicturesFactory::class,
            Model\OffersProperties::class         => Factory\Model\OffersPropertiesFactory::class,
            Model\OffersPropertiesValues::class   => Factory\Model\OffersPropertiesValuesFactory::class,
            Model\OffersPropertiesDefaults::class => Factory\Model\OffersPropertiesDefaultsFactory::class,
            Model\Groups::class                   => Factory\Model\GroupsFactory::class,
            Model\Items::class                    => Factory\Model\ItemsFactory::class,
            Model\Stock::class                    => Factory\Model\StockFactory::class,
            Model\Price::class                    => Factory\Model\PriceFactory::class,
            Model\Offers::class                   => Factory\Model\OffersFactory::class,
            Model\Cart::class                     => Factory\Model\CartFactory::class,
            Model\Orders::class                   => Factory\Model\OrdersFactory::class,
            Model\Related::class                  => Factory\Model\RelatedFactory::class,
            Model\Favorites::class                => Factory\Model\FavoritesFactory::class,
            Model\Discount::class                 => Factory\Model\DiscountFactory::class,
            Model\Coupons::class                  => Factory\Model\CouponsFactory::class,
            Model\Sync::class                     => Factory\Model\SyncFactory::class,
            Model\Feedbacks::class                => Factory\Model\FeedbacksFactory::class
        ]
    ],

    'view_helpers' => [
        'factories'  => [
            View\Helper\CatalogItemsWidget::class => Factory\View\Helper\CatalogItemsWidgetFactory::class
        ],
        'invokables' => [
            View\Helper\CatalogCartWidget::class,
            View\Helper\CatalogSearchWidget::class
        ],
        'aliases'    => [
            'catalogCartWidget'   => View\Helper\CatalogCartWidget::class,
            'catalogSearchWidget' => View\Helper\CatalogSearchWidget::class,
            'catalogItemsWidget'  => View\Helper\CatalogItemsWidget::class
        ]
    ],


    'route_manager' => [
        'factories' => [
            Router\CatalogRouter::class => Factory\Router\CatalogRouterFactory::class
        ]
    ],


    'router' => [
        'routes' => [
            'catalogrouter' => [
                'type'    => Router\CatalogRouter::class,
                'options' => [
                    'defaults' => [
                        'controller' => Controller\ItemsController::class,
                        'action'     => 'list',
                        'category'   => 1,
                    ]
                ]
            ],
            'catalog'       => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/catalog',
                    'defaults' => [
                        'controller' => Controller\ItemsController::class,
                        'action'     => 'list',
                        'category'   => 1,
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'search'    => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/search',
                            'defaults' => [
                                'controller' => Controller\ItemsController::class,
                                'action'     => 'search',
                            ]
                        ],
                    ],
                    'autosync'  => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/autosync',
                            'defaults' => [
                                'controller' => Controller\ItemsController::class,
                                'action'     => 'autosync',
                            ]
                        ],
                    ],
                    'synclog'   => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/synclog',
                            'defaults' => [
                                'controller' => Controller\ItemsController::class,
                                'action'     => 'synclog',
                            ]
                        ],
                    ],
                    'orders'    => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/orders',
                            'defaults' => [
                                'controller' => Controller\OrdersController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'        => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'additem'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/additem/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'additem'
                                    ]
                                ]
                            ],
                            'edit'       => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'update'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/update/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'update'
                                    ]
                                ]
                            ],
                            'list'       => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'action' => 'list'
                                    ]
                                ]
                            ],
                            'getitems'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getitems',
                                    'defaults' => [
                                        'action' => 'getitems'
                                    ]
                                ]
                            ],
                            'getdata'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/getdata/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'getdata'
                                    ]
                                ]
                            ],
                            'view'       => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/view/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'view'
                                    ]
                                ]
                            ],
                            'delete'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'success'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/success/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'success'
                                    ]
                                ]
                            ],
                            'pay'        => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/pay/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'pay'
                                    ]
                                ]
                            ],
                            'result'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/result',
                                    'defaults' => [
                                        'action' => 'result'
                                    ]
                                ]
                            ],
                            'payfail'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/payfail',
                                    'defaults' => [
                                        'action' => 'payfail'
                                    ]
                                ]
                            ],
                            'paysuccess' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/paysuccess',
                                    'defaults' => [
                                        'action' => 'paysuccess'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'cart'      => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/cart',
                            'defaults' => [
                                'controller' => Controller\CartController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'         => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'getstatus'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getstatus',
                                    'defaults' => [
                                        'action' => 'getstatus'
                                    ]
                                ]
                            ],
                            'getlist'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getlist',
                                    'defaults' => [
                                        'action' => 'getlist'
                                    ]
                                ]
                            ],
                            'applycoupon' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/applycoupon',
                                    'defaults' => [
                                        'action' => 'applycoupon'
                                    ]
                                ]
                            ],
                            'update'      => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
                                        'action' => 'update'
                                    ]
                                ]
                            ],
                            'clear'       => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/clear',
                                    'defaults' => [
                                        'action' => 'clear'
                                    ]
                                ]
                            ],
                            'delete'      => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/delete/[:token]',
                                    'defaults' => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'favorites' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/favorites',
                            'defaults' => [
                                'controller' => Controller\FavoritesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'       => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/add/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'getstatus' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getstatus',
                                    'defaults' => [
                                        'action' => 'getstatus'
                                    ]
                                ]
                            ],
                            'getlist'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getlist',
                                    'defaults' => [
                                        'action' => 'getlist'
                                    ]
                                ]
                            ],
                            'clear'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/clear',
                                    'defaults' => [
                                        'action' => 'clear'
                                    ]
                                ]
                            ],
                            'delete'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'feedback' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/feedback',
                            'defaults' => [
                                'controller' => Controller\FeedbackController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'          => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'         => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'itd' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'       => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'getlist'      => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/getlist[/:item]',
                                    'constraints' => [
                                        'item' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'getlist'
                                    ]
                                ]
                            ],
                            'getadminlist' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getadminlist',
                                    'defaults' => [
                                        'action' => 'getadminlist'
                                    ]
                                ]
                            ]

                        ]
                    ],

                    'offers'                     => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/offers',
                            'defaults' => [
                                'controller' => Controller\OffersController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/add/[:item]',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'   => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'pictures'                   => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/pictures',
                            'defaults' => [
                                'controller' => Controller\PicturesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/add/[:item]',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'items-properties'           => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/items-properties',
                            'defaults' => [
                                'controller' => Controller\ItemsPropertiesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'items-properties-defaults'  => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/items-properties-defaults',
                            'defaults' => [
                                'controller' => Controller\ItemsPropertiesDefaultsController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'list'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/[:property]',
                                    'constraints' => [
                                        'property' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'add'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'offers-properties'          => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/offers-properties',
                            'defaults' => [
                                'controller' => Controller\OffersPropertiesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'offers-properties-defaults' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/offers-properties-defaults',
                            'defaults' => [
                                'controller' => Controller\OffersPropertiesDefaultsController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'list'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/[:property]',
                                    'constraints' => [
                                        'property' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'add'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'items' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/items',
                            'defaults' => [
                                'controller' => Controller\ItemsController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'view'           => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/view/[:item]',
                                    'constraints' => [
                                        'item' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'view'
                                    ]
                                ]
                            ],
                            'add'            => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'           => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'         => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/delete',
                                    'defaults' => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'add-related'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add-related',
                                    'defaults' => [
                                        'action' => 'add-related'
                                    ]
                                ]
                            ],
                            'delete-related' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete-related/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete-related'
                                    ]
                                ]
                            ],
                            'getlist'        => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/getlist',
                                    'defaults' => [
                                        'action' => 'getlist'
                                    ]
                                ]
                            ],
                            'getitems'       => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'    => '/getitems[/:group]',
                                    'defaults' => [
                                        'action' => 'getitems'
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'groups'     => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/groups',
                            'defaults' => [
                                'controller' => Controller\GroupsController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ],
                            'add'     => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'currencies' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/currencies',
                            'defaults' => [
                                'controller' => Controller\CurrenciesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'   => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'discount'   => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/discount',
                            'defaults' => [
                                'controller' => Controller\DiscountController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'   => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'coupons'    => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/coupons',
                            'defaults' => [
                                'controller' => Controller\CouponsController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'action' => 'add'
                                    ]
                                ]
                            ],
                            'edit'   => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'categories' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/categories',
                            'defaults' => [
                                'controller' => Controller\CategoriesController::class,
                                'action'     => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'add'     => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/add/[:parent]',
                                    'constraints' => [
                                        'parent' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'add',
                                        'parent' => 1
                                    ]
                                ]
                            ],
                            'edit'    => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/edit/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'delete'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/delete/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'delete'
                                    ]
                                ]
                            ],
                            'getinfo' => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/getinfo/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'getinfo'
                                    ]
                                ]
                            ],
                            'toggle'  => [
                                'type'    => Segment::class,
                                'options' => [
                                    'route'       => '/toggle/[:id]',
                                    'constraints' => [
                                        'id' => '[0-9]*'
                                    ],
                                    'defaults'    => [
                                        'action' => 'toggle'
                                    ]
                                ]
                            ],
                            'reorder' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/reorder',
                                    'defaults' => [
                                        'action' => 'reorder'
                                    ]
                                ]
                            ],
                            'gettree' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/gettree',
                                    'defaults' => [
                                        'action' => 'gettree'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ]
    ]
];
