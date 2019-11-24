<?php

declare(strict_types=1);

use AbterPhp\Admin\Config\Routes as RoutesConfig;
use AbterPhp\Admin\Constant\Routes as RoutesConstant;
use Opulence\Routing\Router;

/**
 * ----------------------------------------------------------
 * Create all of the routes for the HTTP kernel
 * ----------------------------------------------------------
 *
 * @var Router $router
 */
$router->group(
    ['controllerNamespace' => 'AbterPhp\Admin\\Http\\Controllers'],
    function (Router $router) {
        /** @see \AbterPhp\Admin\Http\Controllers\Admin\Form\Login::display() */
        $router->get(
            RoutesConfig::getLoginPath(),
            'Admin\Form\Login@display',
            [RoutesConstant::OPTION_NAME => RoutesConstant::ROUTE_LOGIN]
        );

        /** @see \AbterPhp\Admin\Http\Controllers\Admin\Execute\Login::execute() */
        $router->post(
            RoutesConfig::getLoginPath(),
            'Admin\Execute\Login@execute',
            [RoutesConstant::OPTION_NAME => RoutesConstant::ROUTE_LOGIN_POST]
        );

        /** @see \AbterPhp\Admin\Http\Controllers\Admin\Execute\Logout::execute() */
        $router->get(
            RoutesConstant::PATH_LOGOUT,
            'Admin\Execute\Logout@execute',
            [RoutesConstant::OPTION_NAME => RoutesConstant::ROUTE_LOGOUT]
        );
    }
);
