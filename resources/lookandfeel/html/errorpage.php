<?php

/**
 * Default error page. Override it by defining a page with key ERRORPAGE,
 * For instance, if you have created a look and feel for your not found page called errorpage.php, you could add it
 * to your app like this:
 * ->addPage([ERRORPAGE],['errorpage.php'])
 */
?>
<div style="margin-top: 50px;">
    <h1>Error Page</h1>
    <div>
        <h3><?php echo($feedback ?? 'Your request cannot be fulfilled right now.');?></h3>
    </div>
</div>
