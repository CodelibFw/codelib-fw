<?php
/**
 * AppConfig.php
 */
namespace app\core;
/*
 * AppConfig.php
 * Copyright 2021 {COPYRIGHT_NOTICE}
 *
 */

use cl\ui\web\CLHtmlCtrl;
use cl\ui\web\CLHtmlPage;
use cl\util\Util;
use cl\web\CLConfig;
use cl\web\CLDeployment;
use cl\web\CLHtmlApp;

/**
 * Class AppConfig
 * Configuration class for your CL app
 * @package app
 */
class AppConfig extends CLConfig {
    public function __construct()
    {
        parent::__construct();
        $this->setCSRFStyle(CLREQUEST);
    }

    public function defineFlow(CLHtmlApp $app) : CLConfig {
        // define app pages
        $page1 = new CLHtmlPage();
        // set l&f for the page (this will add a heading which will include the <html><head> and first part of the <body> tags
        $page1->setLookandFeel('header.php');
        // add additional content to the page, in the form of specific controls with their own l&f
        $page1->addElement((new CLHtmlCtrl(''))->setLookandFeel('about.php'));
        $page1->addElement((new CLHtmlCtrl(''))->setLookandFeel('footer.php'));
        // now we can add the page to our app, and assign this configuration to an initial deployment (dev)
        // (you can later add as many deployment types and configurations as you want, and make any of them the active deployment)
        $app
            ->addElement('pg1', $page1, true)
            ->setDeployment(new CLDeployment(CLDeployment::DEV, $this));
        //lgcall$this->loginComp($app);endlgcall
        return $this;
    }

    // loginComp
    private function loginComp($app) {
        $page2 = Util::newPage('header.php', array(array('','register.php'), array('','footer.php')));
        $page3 = new CLHtmlPage();
        $page3->setLookandFeel('header.php');
        $page3->addElement((new CLHtmlCtrl(''))->setLookandFeel('successpage.php'));
        $page3->addElement((new CLHtmlCtrl(''))->setLookandFeel('footer.php'));
        $app->addElement('reg_page', $page2)
            ->addPlugin('user.*', 'CLUserPlugin')
            ->addElement(array('success', 'user.login'), $page3);
    }
    // end of LoginComp
}
