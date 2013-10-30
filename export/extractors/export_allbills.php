<?php

class courseshop_export_source_allbills{
	
	function get_data_description(&$params){
		
		$catalogue = get_record('courseshop_catalog', 'id', $params->config->catalogue);
		
		$desc['filename'] = get_string('allbillsfile', 'block_courseshop', $catalogue->name);
		$desc['title'] = get_string('allbills', 'block_courseshop');
		$desc['colheadingformat'] = 'bold';
		$desc['columns'] = array(
			array('name' => 'transactionid',
			      'width' => 40,
			      'format' => 'smalltext'),
			array('name' => 'onlinetransactionid',
			      'width' => 40,
			      'format' => 'smalltext'),
			array('name' => 'idnumber',
			      'width' => 10,
			      'format' => 'smalltext'),
			array('name' => 'title',
			      'width' => 40,
			      'format' => 'smalltext'),
			array('name' => 'worktype',
			      'width' => 15,
			      'format' => 'smalltext'),
			array('name' => 'status',
			      'width' => 10,
			      'format' => 'smalltext'),
			array('name' => 'emissiondate',
			      'width' => 15,
			      'format' => 'date'),
			array('name' => 'lastactiondate',
			      'width' => 15,
			      'format' => 'date'),
			array('name' => 'untaxedamount',
			      'width' => 10,
			      'format' => 'float'),
			array('name' => 'taxes',
			      'width' => 10,
			      'format' => 'float'),
			array('name' => 'amount',
			      'width' => 10,
			      'format' => 'float'),
			array('name' => 'items',
			      'width' => 60,
			      'format' => 'smalltext'),
			array('name' => 'firstname',
			      'width' => 20,
			      'format' => 'smalltext'),
			array('name' => 'lastname',
			      'width' => 20,
			      'format' => 'smalltext'),
			array('name' => 'address1',
			      'width' => 40,
			      'format' => 'smalltext'),
			array('name' => 'city',
			      'width' => 15,
			      'format' => 'smalltext'),
			array('name' => 'zip',
			      'width' => 8,
			      'format' => 'smalltext'),
			array('name' => 'country',
			      'width' => 8,
			      'format' => 'smalltext'),
			array('name' => 'email',
			      'width' => 20,
			      'format' => 'smalltext'),
			array('name' => 'hasaccount',
			      'width' => 0, /* ignore */
			      'format' => 'smalltext'),
			array('name' => 'username',
			      'width' => 20,
			      'format' => 'smalltext'),
		);
		return array($desc);
	}

	/**
	*
	*/
	function get_data(&$params){
		global $DB, $CFG;
		
		$sql = "
			SELECT
			    b.transactionid,
			    b.onlinetransactionid,
			    b.idnumber,
				b.title,
				b.worktype,
				b.status,
				b.emissiondate,
				b.lastactiondate,
				b.untaxedamount,
				b.taxes,
				b.amount,
				GROUP_CONCAT(bi.itemcode ORDER BY bi.ordering SEPARATOR ',') as items,
				c.firstname,
				c.lastname,
				c.address1,
				c.city,
				c.zip,
				c.country,
				c.email,
				c.hasaccount,
				u.username
			FROM
				{$CFG->prefix}courseshop_bill b,
				{$CFG->prefix}courseshop_billitem bi,
				{$CFG->prefix}courseshop_catalogitem ci,
				{$CFG->prefix}courseshop_customer c
			LEFT JOIN
				{$CFG->prefix}user u
			ON
				c.hasaccount = u.id				
			WHERE
				bi.billid = b.id AND
				b.userid = c.id AND
				ci.code = bi.itemcode AND
				ci.catalogid = {$params->config->catalogue}
			GROUP BY
				b.id
			ORDER BY
				b.ordering
		";
		
		$data = get_records_sql($sql);

		return array($data);
	}		
}