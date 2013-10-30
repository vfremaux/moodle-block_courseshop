<?php

    include '../../../config.php';
    include_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

    header("Content-type: text/javascript");

    $id = required_param('id', PARAM_INT);
    $pinned = optional_param('pinned', '', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
    $context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);

    // get active catalog from block 

    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
    } else {
        error('This block is not configured');
    }

    include $CFG->dirroot.'/blocks/courseshop/classes/Catalog.class.php';
    $theCatalog = new Catalog($catalogid);

    $categories = courseshop_get_categories($theCatalog);

    $shopproducts = courseshop_get_all_products($categories, $theCatalog);

?>

function openPopup(target){
   win = window.open(target, "product", "width=400,height=500,toolbar=0,menubar=0,statusbar=0");
}

function openSalesPopup(){
   win = window.open("<?php echo $CFG->wwwroot ?>/blocks/courseshop/popup.php?p=sales", "sales", "width=600,height=600,toolbar=0,menubar=0,statusbar=0, resizable=1,scrollbars=1");
}

function addOneUnit(target, code, price, maxdelivery){

    var formElement = document.caddie.elements[target];

    if (maxdelivery == 0 || parseInt(formElement.value) < maxdelivery){
        formElement.value = parseInt(formElement.value) + 1;
        calculateLocal(formElement,code,price);
        totalize();
    } else {
        alert('<?php print_string('maxdeliveryreached', 'block_courseshop') ?>');
    }
}

function calculateLocal(obj, code, amount){

   	var localtotal = amount * obj.value;

   	document.caddie.elements[obj.name + "_total"].value = localtotal.toFixed(2);
   	var spanElement = document.getElementById('bag_' + obj.name);
   	tmp = "";
   	for(i = 0 ; i < obj.value ; i++){
      	tmp += '&nbsp;<img src="<?php echo $CFG->wwwroot ?>/blocks/courseshop/shop/productthumb.php?code='+code+'" align="middle">';
    }
   	spanElement.innerHTML = tmp;
   
   	ordertotal_caption = document.getElementById('producttotalcaption_' + obj.name);
   	ordertotal_line = document.getElementById('producttotal_' + obj.name);
   
   	if (localtotal){
	   ordertotal_caption.style.display = 'block';
	   ordertotal_line.style.display = 'block';
	} else {
	   ordertotal_caption.style.display = 'none';
	   ordertotal_line.style.display = 'none';
	}
   
}

function totalize(){
   var objectCount =
<?php
foreach($categories as $aCategory){
    foreach($aCategory->products as $aProduct){
        // explode set content in caddie
        if ($aProduct->isset === 1){
            foreach($aProduct->set as $aProduct){
?>
      eval(document.caddie.<?php echo $aProduct->shortname ?>.value) +
<?php
            }
        } else {
?>
      eval(document.caddie.<?php echo $aProduct->shortname ?>.value) +
<?php            
        }
    }
}
?>
      0;
   total_value = 
<?php
foreach($categories as $cat){
    foreach($cat->products as $aProduct){
        if ($aProduct->isset === 1){
            foreach($aProduct->set as $aProduct){
?>
      eval(parseFloat(0 + document.caddie.<?php echo $aProduct->shortname ?>_total.value)) +
<?php
            }
        } else {
?>
      eval(parseFloat(0 + document.caddie.<?php echo $aProduct->shortname ?>_total.value)) +
<?php
        }
    }
}
?>
      0;
   document.caddie.totalEurosTTC.value = total_value.toFixed(2);
<?php
if (!empty($CFG->block_courseshop_usediscountthreshold)){
?>
   if (document.caddie.totalEurosTTC.value > <?php echo $CFG->block_courseshop_discountthreshold ?>){
      document.caddie.discounted.value = Math.round(eval(document.caddie.totalEurosTTC.value) * 85) / 100;
   } else {
      document.caddie.discounted.value = 0 + document.caddie.totalEurosTTC.value;
   }
<?php
} else {
?>
      document.caddie.discounted.value = 0 + document.caddie.totalEurosTTC.value;
<?php
}
?>
   totalSpan = document.getElementById('total_euros_span');
   objCountSpan = document.getElementById('object_count_span');
   discountedSpan = document.getElementById('courseshop-discounted-span');
   totalSpan.innerHTML = parseFloat(document.caddie.totalEurosTTC.value).toFixed(2);
   objCountSpan.innerHTML = objectCount;
   discountedSpan.innerHTML = parseFloat(document.caddie.discounted.value).toFixed(2) ;
   
	paymentDiv = document.getElementById('courseshop-paymodes');
	if (total_count > 0){
   		paymentDiv.style.opacity = 1.0;
	} else {
   		paymentDiv.style.opacity = 0.5;
	}
}

/**
* checks if there are some products in the basket (caddie).
*
*/
function checkZeroEuroCommand(){
	var itemscount = document.caddie.elements.length;
    if (itemscount){
        checkedformlaunch('caddie');
    } else {
       alert("<?php print_string('emptybasket', 'block_courseshop') ?>");
    }
    return false;
}

/**
* Toggles product line category panels
*
*/
function showcategory(catid, allids){

	allidsarr = allids.split(',');
	for (hidecatid in allidsarr){
		toshowtabid = 'catli' + allidsarr[hidecatid];

		toshowtab = document.getElementById(toshowtabid);
		toshowtab.className = 'onerow';
	
		tohide = document.getElementById('category' + allidsarr[hidecatid]);
		tohide.style.visibility = 'hidden';
		tohide.style.display = 'none';
	}

	toshowtabid = 'catli'+catid;

	toshowtab = document.getElementById(toshowtabid);
	toshowtab.className = 'onerow here';

	toshow = document.getElementById('category'+catid);
	toshow.style.visibility = 'visible';
	toshow.style.display = 'block';
}
