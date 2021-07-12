<?php

/**
 * Default error page. Override it by calling setErrorPage($page) in your CLHtmlApp,
 * where page is the page you defined as your error page.
 * For instance, if you have created a look and feel for your error page called myerrorpage.php, you could add it like this:
 * use cl\util\Util;
 * use cl\web\CLHtmlApp;
 * $errorpage = Util::newPage('myerrorpage.php');
 * $myapp = new CLHtmlApp();
 * $myapp->setErrorPage($errorpage);
 */
?>
<div style="margin-top: 50px;">
    <h1>Error Page</h1>
    <div>
        <h3><?php echo($feedback);?></h3>
    </div>
</div>
