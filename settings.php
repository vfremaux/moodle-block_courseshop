<?php

require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

$currencies = courseshop_get_supported_currencies();

$settings->add(new admin_setting_configselect('block_courseshop_defaultcurrency', get_string('defaultcurrency', 'block_courseshop'),
                   get_string('configdefaultcurrency', 'block_courseshop'), @$CFG->block_courseshop_defaultcurrency, $currencies));

$settings->add(new admin_setting_configselect('block_courseshop_defaultcountry', get_string('defaultcountry', 'block_courseshop'),
                   get_string('configdefaultcountry', 'block_courseshop'), @$CFG->block_courseshop_defaultcountry, get_list_of_countries()));

$settings->add(new admin_setting_configtext('block_courseshop_discountthreshold', get_string('discountthreshold', 'block_courseshop'),
                   get_string('configdiscounttheshold', 'block_courseshop'), 0, PARAM_INT));

$settings->add(new admin_setting_configtext('block_courseshop_discountrate', get_string('discountrate', 'block_courseshop'),
                   get_string('configdiscountrate', 'block_courseshop'), 0, PARAM_INT));

$settings->add(new admin_setting_configselect('block_courseshop_test', get_string('testmode', 'block_courseshop'),
                   get_string('configtestmode', 'block_courseshop'), 0, array('0' => get_string('no'), '1' => get_string('yes'))));

$settings->add(new admin_setting_configselect('block_courseshop_useshipping', get_string('useshipping', 'block_courseshop'),
                   get_string('configuseshipping', 'block_courseshop'), 0, array('0' => get_string('no'), '1' => get_string('yes'))));

$settings->add(new admin_setting_heading('block_courseshop_vendor', get_string('vendorinfo', 'block_courseshop'), ''));

$settings->add(new admin_setting_configtext('block_courseshop_sellername', get_string('sellername', 'block_courseshop'),
                   get_string('configsellername', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_selleraddress', get_string('selleraddress', 'block_courseshop'),
                   get_string('configselleraddress', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerzip', get_string('sellerzip', 'block_courseshop'),
                   get_string('configsellerzip', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellercity', get_string('sellercity', 'block_courseshop'),
                   get_string('configsellercity', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellercountry', get_string('sellercountry', 'block_courseshop'),
                   get_string('configsellercountry', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellermail', get_string('sellermail', 'block_courseshop'),
                   get_string('configsellermail', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellermailsupport', get_string('sellermailsupport', 'block_courseshop'),
                   get_string('configsellermailsupport', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerphonesupport', get_string('sellerphonesupport', 'block_courseshop'),
                   get_string('configsellerphonesupport', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerID', get_string('sellerID', 'block_courseshop'),
                   get_string('configsellerID', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerbillingaddress', get_string('sellerbillingaddress', 'block_courseshop'),
                   get_string('configsellerbillingaddress', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerbillingzip', get_string('sellerbillingzip', 'block_courseshop'),
                   get_string('configsellerbillingzip', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerbillingcity', get_string('sellerbillingcity', 'block_courseshop'),
                   get_string('configsellerbillingcity', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_sellerbillingcountry', get_string('sellerbillingcountry', 'block_courseshop'),
                   get_string('configsellerbillingcountry', 'block_courseshop'), '', PARAM_TEXT));

courseshop_paymode::courseshop_add_paymode_settings($settings);

$settings->add(new admin_setting_heading('block_courseshop_bankinginfo', get_string('bankinginfo', 'block_courseshop'), ''));

$settings->add(new admin_setting_configtext('block_courseshop_banking', get_string('banking', 'block_courseshop'),
                   get_string('configbanking', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_bankcode', get_string('bankcode', 'block_courseshop'),
                   get_string('configbankcode', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_bankoffice', get_string('bankoffice', 'block_courseshop'),
                   get_string('configbankoffice', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_bankaccount', get_string('bankaccount', 'block_courseshop'),
                   get_string('configbankaccount', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_bankaccountkey', get_string('bankaccountkey', 'block_courseshop'),
                   get_string('configbankaccountkey', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_iban', get_string('iban', 'block_courseshop'),
                   get_string('configiban', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_bic', get_string('bic', 'block_courseshop'),
                   get_string('configbic', 'block_courseshop'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('block_courseshop_tvaeurope', get_string('tvaeurope', 'block_courseshop'),
                   get_string('configtvaeurope', 'block_courseshop'), '', PARAM_TEXT));

?>