<?php

global $CFG;

$string['productiondata_public'] = "
<p>Your user account has been opened on this platform. An email has been sent to you with your
credentials inside.</p>
<p>If you have paied online, tour products will be setup and enabled immediately after we receive
electronic confirmation of your payment. You will be able to start immediately your training. In case you paied using an offline
method, your products will be activated as soon as we process your paiment.</p>
<p><a href=\\\"{$CFG->wwwroot}/login/index.php\\\">Login to the training site</a></p>
";

$string['productiondata_private'] = "
<p>Your user account has been opened on this site.</p>
<p>Your credentials are:<br/>
Login: \$a->username<br/>
Password: \$a->password<br/></p>
<p><b>PLease keep them in some safe location you can recover them when needed...</b></p>
<p>If you have paied using an online method, your products have been enabled automatically on electronic confirmation of your
payment provider. You may connect immediately and start your training. In case you used an offline method, your products will be enabled
as soon as we process your paiment.</p>
<p><a href=\\\"{$CFG->wwwroot}/login/index.php\\\">Login to the traning site</a></p>
";

$string['productiondata_sales'] = "
<p>A user acocunt has been aded to the platform.</p>
<p>Username:<br/>
Login: \$a<br/>
";

$string['productiondata_assign_public'] = "
<p><b>Payment complete</b></p>
<p>Your paiement has been fullfitted. Your enrolment is now complete. You can access your traning material with the 
credentials you received.</p>
";

$string['productiondata_assign_private'] = "
<p><b>Payment complete</b></p>
<p>Your paiement has been fullfitted. Your enrolment is now complete. You can access your traning material with the 
credentials you received.</p>
<p><a href=\\\"{$CFG->wwwroot}/course/view.php?id=\$a\\\">Access your training right now</a></p>
";

$string['productiondata_assign_sales'] = "
<p><b>Customer Payment complete</b></p>
<p>Customer accesses on course have been opened.</p>
<p><a href=\\\"{$CFG->wwwroot}/course/view.php?id=\$a\\\">Go to corresponding course</a></p>
";

?>