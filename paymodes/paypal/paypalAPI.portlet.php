<?php
$lang = substr(current_language(), 0, 2);
if ($lang == 'en') $lang = 'us';
$ulang = strtoupper($lang);
if (!empty($CFG->block_courseshop_test)) {
?>
<table class="width500" style="border : 2px solid red">
    <tr>
        <td align="center">
           <span class="error"><?php print_string('testmode', 'block_courseshop') ?></span><br />
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display : inline">
            <!-- input type="hidden" name="cmd" value="_xclick" -->
            <input type="hidden" name="cmd" value="_ext-enter">
            <input type="hidden" name="redirect_cmd" value="_xclick">   
            <input type="hidden" name="business" value="<?php echo $CFG->block_courseshop_paypalsellertestname ?>">
            <input type="hidden" name="item_name" value="<?php echo $CFG->block_courseshop_sellertestitemname ?>">
            <input type="hidden" name="item_number" value="">
<?php
} else {
?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display : inline">
<!-- input type="hidden" name="cmd" value="_xclick" -->
<input type="hidden" name="cmd" value="_ext-enter">
<input type="hidden" name="redirect_cmd" value="_xclick">   

<input type="hidden" name="business" value="<?php echo $CFG->block_courseshop_paypalsellername ?>">
<input type="hidden" name="item_name" value="<?php echo $CFG->block_courseshop_selleritemname ?>">
<?php
}
?>
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="amount" value="<?php echo $portlet->amount ?>">
<input type="image" src="http://www.paypal.com/<?php echo $lang ?>_<?php echo $ulang ?>/i/btn/x-click-but01.gif" name="submit" 
   alt="<?php print_string('paypalmsg', 'block_courseshop') ?>">
<input type="hidden" name="quantity" value="1">
<input type="hidden" name="item_number" value="">
<input type="hidden" name="shipping" value="">
<!-- input type="hidden" name="shipping2" value="" --> 
<!-- input type="hidden" name="handling" value="" --> 
<!-- input type="hidden" name="tax" value="" --> 
<input type="hidden" name="no_shipping" value="1" >
<!-- input type="hidden" name="cn" value="" -->
<input type="hidden" name="no_note" value="1">  
<!-- input type="hidden" name="on0" value="" -->
<!-- input type="hidden" name="os0" value="" -->
<!-- input type="hidden" name="on1" value="" -->
<!-- input type="hidden" name="os1" value="" -->
<input type="hidden" name="custom" value="<?php echo $this->shopblock->instance->id ?>-<?php echo $this->shopblock->pinned ?>">
<input type="hidden" name="invoice" value="<?php echo $portlet->transactionid ?>">
<input type="hidden" name="notify_url" value="<?php echo $CFG->wwwroot ?>/blocks/courseshop/paymodes/paypal/paypal_ipn.php">
<input type="hidden" name="return" value="<?php echo $CFG->wwwroot ?>/blocks/courseshop/paymodes/paypal/process.php?id=<?php echo $this->shopblock->instance->id ?>&pinned=<?php echo $this->shopblock->pinned ?>&transid=<?php echo $portlet->transactionid ?>">
<input type="hidden" name="rm" value="2">
<input type="hidden" name="cancel_return" value="<?php echo $CFG->wwwroot ?>/blocks/courseshop/paymodes/paypal/cancel.php?id=<?php echo $this->shopblock->instance->id ?>&pinned=<?php echo $this->shopblock->pinned ?>&transid=<?php echo $portlet->transactionid ?>">
<input type="hidden" name="image_url" value="">
<input type="hidden" name="cs" value="1">

<input type="hidden" name="email" value="<?php echo $portlet->mail ?>">
<input type="hidden" name="first_name" value="<?php echo $portlet->firstname ?>">
<input type="hidden" name="last_name" value="<?php echo $portlet->lastname ?>">
<input type="hidden" name="address1" value="">
<input type="hidden" name="address2" value="">
<input type="hidden" name="city" value="<?php echo $portlet->city ?>">
<input type="hidden" name="state" value="<?php echo $portlet->country ?>">
<input type="hidden" name="zip" value="">

<!-- input type="hidden" name="night_phone_a" value="1">
<input type="hidden" name="night_phone_b" value="1">
<input type="hidden" name="day_phone_a" value="1">
<input type="hidden" name="day_phone_b" value="1" -->
</form>
<?php
if (!empty($CFG->block_courseshop_test)) {
?>
        </td>
     </tr>
</table>
<?php
}
?>