<?php

namespace App\Controllers;

use \Core\View;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction($params)
    {
        $mainArgs = [
            'title' => 'Fixed navbar',
            'description' => 'Fixed navbar: Bootstrap 4 example template',
            'author' => 'Twitter',
            'menu' => [
                [
                    'title' => 'Home',
                    'class' => 'active',
                    'href' => '#',
                ],
                [
                    'title' => 'Link',
                    'class' => null,
                    'href' => '#',
                ],
                [
                    'title' => 'Disabled',
                    'class' => 'disabled',
                    'href' => '#',
                ],
            ],
        ];
        $bootstrapArgs = [
            'navbar' => View::render('navbar.phtml', $mainArgs),
            'main' => View::render('Home/index.phtml', $mainArgs),
        ];
        echo View::render('bootstrap.phtml', $mainArgs + $bootstrapArgs);
    }
}
