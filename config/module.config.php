<?php

return array(
    'view_manager' => array(
        'template_path_stack' => array(
            'IsAvailRegistroBR' => __DIR__ . '/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            'is-avail' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/is-avail',
                    'defaults' => array(
                        'controller' => 'IsAvailRegistroBR\Controller\IsAvail',
                        'action'     => 'index',
                    ),
                ),
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'IsAvailRegistroBR\Controller\IsAvail' => 'IsAvailRegistroBR\Controller\IsAvailController'
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'checkAvail' => 'IsAvailRegistroBR\Controller\Plugin\IsAvail',
        )
    ),
);
