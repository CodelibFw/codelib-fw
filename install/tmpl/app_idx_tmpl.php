<?php
namespace app;

use app\core\AppConfig;
use cl\web\CLHtmlApp;

define('BASE_DIR', ${basedir}); // app base dir. Points to your app/src/ folder (including '/' at the end).
define('CL_DIR', '${cldir}'); // code-lib dir as absolute path. Perhaps changed it to a path relative to your
                              // BASE_DIR, like: define('CL_DIR', BASE_DIR.'/../code-lib/src'.DIRECTORY_SEPARATOR);
                              // depending on where CL is located in your computer relative to your App.
                              // In that way, if you deploy your App and CL, and keep the same relative position for
                              // them, in your Server, no changes are required here.
define('APP_NS', __NAMESPACE__);
require CL_DIR . 'cl/CLStart.php';
// these files belong to your app
$app = new CLHtmlApp(); // this is your application instance
// this is just one way to define your configuration. See samples for alternative ways
$config = new AppConfig(); // this is your configuration, where you can define your app flow, db connections, etc...
$config->defineFlow($app); // here you apply your configuration to your app
$app->run(); // and finally you run your app (there is an optional bool parameter, if you wish to run it in diagnostics mode
// if you do run your app in diagnostics mode, you could view your report in your browser by uncommenting these lines:
//$inspection = $app->getInspection();
//$report = $inspection->getReport();
// echo($report);
// the report should give you an idea of what CodeLib knows about your app, and perhaps you can spot an unhandled flow, etc
