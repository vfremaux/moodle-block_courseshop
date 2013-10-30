<?php  //$Id: upgrade.php,v 1.3 2013-02-09 00:07:13 vf Exp $

// This file keeps track of upgrades to 
// the course_summary block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_courseshop_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if ($result && $oldversion < 2010032800) { //New version in version.php
        if (!record_exists('role', 'shortname', 'sales')){
            $result = create_role(get_string('salesrolename', 'block_courseshop'), 'sales', get_string('salesroledesc', 'block_courseshop')) ;
        }
    }    

    if ($result && $oldversion < 2010040700) {

    /// Define field idnumber to be added to courseshop_bill
        $table = new XMLDBTable('courseshop_bill');
        $field = new XMLDBField('idnumber');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'id');

    /// Launch add field idnumber
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2010041500) {

    /// Define table courseshop_paypal_ipn to be created
        $table = new XMLDBTable('courseshop_paypal_ipn');

    /// Adding fields to table courseshop_paypal_ipn
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('transid', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('txnid', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('paypalinfo', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);
        $table->addFieldInfo('result', XMLDB_TYPE_CHAR, '12', null, null, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table courseshop_paypal_ipn
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for courseshop_paypal_ipn
        $result = $result && create_table($table);
    }

    if ($result && $oldversion < 2011060200) {

    /// Define field idnumber to be added to courseshop_bill
        $table = new XMLDBTable('courseshop_bill');

        $field = new XMLDBField('productiondata');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'ignoretax');

    /// Launch add field idnumber
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('paymentfee');
        $field->setAttributes(XMLDB_TYPE_NUMBER, '10, 2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0.00', 'productiondata');

    /// Launch add field idnumber
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2011101000) {

    /// Changing precision of field status on table courseshop_catalogitem to (16)
        $table = new XMLDBTable('courseshop_catalogitem');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, XMLDB_ENUM, array('WORKING', 'PREVIEW', 'AVAILABLE', 'AVAILABLEINTERNAL', 'SUSPENDED', 'PROVIDING', 'ABANDONNED'), 'AVAILABLE', 'categoryid');

    /// Launch change of precision for field status
        $result = $result && change_field_precision($table, $field);
    }

    if ($result && $oldversion < 2012011400) {

    /// Define field enablehandler to be added to courseshop_catalogitem
        $table = new XMLDBTable('courseshop_catalogitem');
        $field = new XMLDBField('enablehandler');
        $field->setAttributes(XMLDB_TYPE_CHAR, '30', null,  null, null, null, null, null, 'requireddata');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2012012100) {

    /// Define field enablehandler to be added to courseshop_catalogitem
        $table = new XMLDBTable('courseshop_catalogitem');
        $field = new XMLDBField('handlerparams');
        $field->setAttributes(XMLDB_TYPE_CHAR, '80', null, null, null, null, null, null, 'enablehandler');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);

    /// Define field customerdata to be added to courseshop_billitem
        $table = new XMLDBTable('courseshop_billitem');
        $field = new XMLDBField('customerdata');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'bundleid');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2013011001) {

    /// Define field sortorder to be added to courseshop_catalogcategory
        $table = new XMLDBTable('courseshop_catalogcategory');
        $field = new XMLDBField('sortorder');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, 0, 'description');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('visible');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,  XMLDB_NOTNULL, null, null, null, 1, 'sortorder');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);
        
        // reorder existing categories
        if ($catalogs = get_records('courseshop_catalog')){
        	foreach($catalogs as $catalog){
        		if ($cats = get_records('courseshop_catalogcategory', 'catalogid', $catalog->id)){
        			$i = 1;
        			foreach($cats as $c){
        				$c->sortorder = $i;
        				update_record('courseshop_catalogcategory', $c);
        				$i++;
        			}
        		}
        	}
        }
    }

    if ($result && $oldversion < 2013011002) {

    /// Define field sortorder to be added to courseshop_catalog
        $table = new XMLDBTable('courseshop_catalog');
        $field = new XMLDBField('countryrestrictions');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, 0, 'groupid');

    /// Launch add field enablehandler
        $result = $result && add_field($table, $field);
        
    }

    if ($result && $oldversion < 2013030300) {

    /// Define table courseshop_product to be created
        $table = new XMLDBTable('courseshop_product');

    /// Adding fields to table courseshop_product
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('catalogitemid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('customerid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('startdate', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('enddate', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('reference', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('productiondata', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table courseshop_product
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table courseshop_product
        $table->addIndexInfo('ix_cs_product_catalogitem', XMLDB_INDEX_NOTUNIQUE, array('catalogitemid'));
        $table->addIndexInfo('ix_cs_product_customer', XMLDB_INDEX_NOTUNIQUE, array('customerid'));

    /// Launch create table for courseshop_product
        $result = $result && create_table($table);

    /// Define table courseshop_product_billitem to be created
        $table = new XMLDBTable('courseshop_product_billitem');

    /// Adding fields to table courseshop_product_billitem
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('productid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('billitemid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('datecreated', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table courseshop_product_billitem
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table courseshop_product_billitem
        $table->addIndexInfo('ix_cs_product_id', XMLDB_INDEX_NOTUNIQUE, array('productid'));
        $table->addIndexInfo('ix_cs_product_billitem', XMLDB_INDEX_NOTUNIQUE, array('billitemid'));

    /// Launch create table for courseshop_product_billitem
        $result = $result && create_table($table);
        
    }

    if ($result && $oldversion < 2013030301) {

    /// Define field renewable to be added to courseshop_catalog_item
        $table = new XMLDBTable('courseshop_catalogitem');
        $field = new XMLDBField('renewable');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'handlerparams');

    /// Launch add field renewable
        $result = $result && add_field($table, $field);

    /// Define field eulaurl to be added to courseshop_catalog_item
        $field = new XMLDBField('eulaurl');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null, 'renewable');

    /// Launch add field eulaurl
        $result = $result && add_field($table, $field);
        
    }

    return $result;
}

?>
