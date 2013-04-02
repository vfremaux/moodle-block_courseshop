<?php

/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    block-courseshop
 * @subpackage shop admin
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * this file introduces the coursehop admininstration layer
 * users will need a block/courseshop:salesadmin capability to operate the shop structure.
 *
 */

    include "../../config.php";
    
    $navlinks = array(array('name' => get_string('salesservice', 'block_courseshop'), 'url' => '' , 'type' => 'title'));

    $navigation = build_navigation($navlinks);

    print_header('courseshop', 'courseshop', $navigation, '', '', '');
    

    $cmd = optional_param('cmd', '', PARAM_TEXT);
    $id = required_param('id', PARAM_INT); // the block ID
    $pinned = optional_param('pinned', false, PARAM_INT); // the block ID

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    if ($cmd != ''){
        include $CFG->dirroot.'/blocks/courseshop/catalogs/catalogs.controller.php';
    }

    $sql = "
       SELECT 
          c.*,
          c.id = c.groupid as ismaster,
          c.id != c.groupid AND c.groupid IS NOT NULL AND c.groupid != 0 as isslave,
          COUNT(ci.id) as items
       FROM
          {$CFG->prefix}courseshop_catalog as c
       LEFT JOIN
          {$CFG->prefix}courseshop_catalogitem as ci
       ON
          ci.catalogid = c.id
       GROUP BY
          c.id
       ORDER BY
          c.groupid ASC,ismaster DESC
    ";
    $catalogs = get_records_sql($sql);
    
?>

<?php print_heading(get_string('salesservice', 'block_courseshop'), 'center', 1) ?>

<?php print_heading(get_string('cataloguemanagement', 'block_courseshop'), 'center', 2) ?>

<p><center>
<table width="90%" cellspacing="10">
    <tr>
        <td valign="top">
            <b><?php print_string('allproducts', 'block_courseshop') ?></b>
        </td>
        <td valign="top">
            <?php print_string('searchinproducts', 'block_courseshop') ?><br>
            <table width="100%" class="generaltable">
                <tr>
                    <th align="left" class="header c0">
                        <?php print_string('name', 'block_courseshop') ?>
                    </th>
                    <th align="left" class="header c1">
                        <?php print_string('description', 'block_courseshop') ?>
                    </th>
                    <th align="left" class="header c2">
                        <?php print_string('items', 'block_courseshop') ?>
                    </th>
                    <th align="left" class="header lastcol">
                        <?php print_string('controls', 'block_courseshop') ?>
                    </th>
                </tr>
<?php
if ($catalogs){
    foreach($catalogs as $portlet){
        include $CFG->dirroot.'/blocks/courseshop/lib/catalogAdminLine.portlet.php';
    }
} else {
}
?>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right">
            <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/catalogs/edit_catalogue.php?id={$id}&pinned={$pinned}" ?>"><?php print_string('newcatalog', 'block_courseshop') ?></a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewAllBills&id=$id&pinned=$pinned" ?>"><?php print_string('allbills', 'block_courseshop') ?></a>
        </td>
        <td>
            <?php print_string('searchinbills', 'block_courseshop') ?>
        </td>
    </tr>
    <tr>
        <td>
            <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/view.php?view=viewAllCustomers&id=$id&pinned=$pinned" ?>"><?php print_string('allcustomers', 'block_courseshop') ?></a>
        </td>
        <td>
            <?php print_string('searchincustomers', 'block_courseshop') ?>
        </td>
    </tr>
	<tr>
		<td>
			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/taxes/view.php?view=viewAllTaxes&id=$id&pinned=$pinned"?>"><?php print_string('managetaxes', 'block_courseshop') ?></a>
		</td>
		<td>
			<?php print_string('searchintaxes', 'block_courseshop') ?>
		</td>
	</tr>
	<tr>
		<td>
			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/shipzones/index.php?id=$id&pinned=$pinned"?>"><?php print_string('manageshipping', 'block_courseshop') ?></a>
		</td>
		<td>
			<?php print_string('manageshippingdesc', 'block_courseshop') ?>
		</td>
	</tr>
	<tr>
		<td>
			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/shop/scantrace.php?id={$id}&pinned={$pinned}"?>"><?php print_string('scantrace', 'block_courseshop') ?></a>
		</td>
		<td>
			<?php print_string('tracescandesc', 'block_courseshop') ?>
		</td>
	</tr>
<?php 
if (has_capability('moodle:site/config', get_context_instance(CONTEXT_SYSTEM))){
?>
	<tr>
		<td>
			<a href="<?php echo $CFG->wwwroot."/admin/settings.php?section=blocksettingcourseshop" ?>"><?php print_string('settings', 'block_courseshop') ?></a>
		</td>
		<td>
			<?php print_string('generalsettings', 'block_courseshop') ?>
		</td>
	</tr>
<?php
}
?>
	<tr>
		<td>
			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/reset.php?id={$id}&pinned={$pinned}" ?>"><?php print_string('reset', 'block_courseshop') ?></a>
		</td>
		<td>
			<?php print_string('resetdesc', 'block_courseshop') ?>
		</td>
	</tr>

</table>

<?php
print_footer();
?>