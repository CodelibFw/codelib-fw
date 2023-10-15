<?php

/**
 * Default not found page. Override it by defining a page with key NOTFOUNDPAGE,
 * For instance, if you have created a look and feel for your not found page called notfoundpage.php, you could add it
 * to your app like this:
 * ->addPage([NOTFOUNDPAGE],['notfoundpage.php'])
 */
?>
<div style="margin-top: 50px;">
    <h1>Not Found Page</h1>
    <div>
        <h3><?php echo($feedback ?? 'What you are looking for does not seem to be here.');?></h3>
    </div>
</div>
