<?php


namespace cl\test;


use cl\contract\CLRequest;
use cl\core\CLRoute;

class CLHtmlAppTest extends \cl\web\CLHtmlApp
{

    public function setup() {
        define('BASE_DIR', __DIR__.'/../');
        define('CL_DIR', BASE_DIR.'/../../code-lib/src'.DIRECTORY_SEPARATOR);
        define('APP_NS', __NAMESPACE__);
    }

    public function submitRequest(CLRequest $request) {

    }

    public function callRoute(CLRoute $route) {

    }
}
