<?php

namespace App\Controllers;

use \Core\View;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class ParametersExample extends \Core\Controller {

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction() {
        View::renderTemplate('ParametersExample/index.html');
    }

    public function oneAction($id) {
        $argsToView = ["id" => $id];
        View::renderTemplate('ParametersExample/one.html', $argsToView);
    }

    public function twoAction($logStatus, $logLevel = "info") {
        $argsToView = array(
            "logStatus" => $logStatus,
            "logLevel" => $logLevel
        );
        View::renderTemplate('ParametersExample/two.html', $argsToView);
    }

}
