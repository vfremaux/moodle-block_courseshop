<h3><%%SELLER%%></h3>
<h4>Confirmation commande client</h4>

<p>La commande ci-dessous a été facturée sur le site <%%SERVER%%> : 

<p><u>Identification Client :</u>
<hr>
<b>Nom :</b> <%%FIRSTNAME%%><br>
<b>Prénom :</b> <%%LASTNAME%%><br>
<b>Mel :</b> <%%MAIL%%><br>
<b>Ville :</b> <%%CITY%%><br>
<b>Pays :</b> <%%COUNTRY%%><br>
<hr>
<p><u>Résumé Commande</u>
<hr>
<b>Montant total H.T. :</b> <%%AMOUNT%%><br>
<b>Taxes :</b> <%%TAXES%%><br>
<b>TTC :</b> <%%TTC%%><br>
<b>Mode de paiement envisagé :</b> <%%PAYMODE%%><br>
<b>Nombre d'objets :</b> <%%ITEMS%%><br>
<b>Code transaction :</b> <code><%%TRANSACTION%%></code>
<hr/>
<%%PRODUCTION_DATA%%>
<hr/>
<p>Examen de la commande (personnes autorisées)
<hr>
<a href="<%%SERVER_URL%%>/login/index.php?ticket=<%%TICKET%%>">Visualiser la commande dans la gestion commerciale</a>