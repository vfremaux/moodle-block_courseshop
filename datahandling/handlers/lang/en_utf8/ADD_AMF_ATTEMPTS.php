<?php

global $CFG;

$string['productiondata_public'] = "
<p>Vous avez obtenu des tentatives $a->extension supplémentaires pour vos examens blancs.</p>
<p>Si vous avez effectué votre paiement en ligne, Votre extension est immédiatement réalisée. Vous pouvez vous connecter
et bénéficier de vos tentatives supplémentaires. Dans le cas contraire vos tentatives seront validées dès réception de votre paiement.</p>
<p><a href=\\\"{$CFG->wwwroot}/course/view.php?id=\$a->courseid\\\">Accéder à la formation</a></p>
";

$string['productiondata_private'] = "
<p>Vous avez obtenu des tentatives $a->extension supplémentaires pour vos examens blancs.</p>
<p>Si vous avez effectué votre paiement en ligne, Votre extension est immédiatement réalisée. Vous pouvez vous connecter
et bénéficier de vos tentatives supplémentaires. Dans le cas contraire vos tentatives seront validées dès réception de votre paiement.</p>
<p><a href=\\\"{$CFG->wwwroot}/course/view.php?id=\$a->courseid\\\">Accéder à la formation</a></p>
";

$string['productiondata_sales'] = "
<p>Le client $a->username a étendu ses tentatives.</p>
<p><a href=\\\"{$CFG->wwwroot}/course/view.php?id=\$a->courseid\\\">Accéder à la formation</a></p>
";

