<?php

use app\core\AppConfig;
use cl\web\CLHtmlApp;

if (isset($_SERVER['CONTENT_TYPE'])) {
    $contenttype = $_SERVER['CONTENT_TYPE'];
    $idx = mb_strpos($contenttype, 'application/json');
    if ($idx !== false) {
        $cnt = file_get_contents('php://input');
        error_log('cnt:' . $cnt);
        $data = json_decode($cnt, true);
        error_log('data decoded: ' . $data['action']);
        if (isset($data['action']) && isset($data['src'])) {
            if ($data['action'] == 'diagn') {
                $appsrc = $data['src'];
                $appmain = $appsrc.'/index.php';
                $cl = __DIR__.'/../src/';
                if (file_exists($appmain)) {
                    chdir($appsrc);
                    if (!isset($_POST)) {
                        $_POST = [];
                    }
                    $_SERVER['CONTENT_TYPE'] = 'text/html';
                    $_POST['clkey'] = 'knowthyself';
                    $installMode = true;
                    include $appmain;
                    if (isset($app)) {
                        $inspection = $app->getInspection();
                        if ($inspection != null) {
                            $report = $inspection->getReport();
                            header('Content-Type: application/json; charset=UTF-8');
                            if ($report != null) {
                                echo json_encode(["feedback" => $report]);
                            } else {
                                echo('{"feedback": "Diagnostic seems to have failed"}');
                            }
                        } else {
                            echo('{"feedback": "Diagnostic seems to have failed"}');
                        }
                    }
                }
            } else
                if ($data['action'] == 'create') {
                    $cl = __DIR__.'/../src/';
                    $cl = realpath($cl). '/'; // using this for both Linux and Windows
                    $cl = str_replace('\\','/', $cl);
                    define('CL_DIR', $cl);
                    $apptype = $data['apptype'];
                    if ($apptype == 'at1') {
                        include_once 'NewAppWzd.php';
                        $appWzd = new NewAppWzd($data);
                        $appStruResult = $appWzd->createAppStructure();
                        if ($appStruResult == 'ok') {
                            $appCreationResult = $appWzd->createApp();
                            if ($appCreationResult == 'ok') {
                                echo('{"feedback": "Application has been successfully created", "result": "ok"}');
                            } else {
                                echo('{"feedback": "Error:' . $appCreationResult . '", "result": "error"}');
                            }
                        } else {
                            echo('{"feedback": "Error:' . $appStruResult . '", "result": "error"}');
                        }
                    } else {

                    }
                }
        }
    }
}

