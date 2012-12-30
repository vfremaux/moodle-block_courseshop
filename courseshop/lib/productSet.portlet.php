<table class="article" width="100%">
   	<tr>
		<td class="productpix" rowspan="<?php echo count($portlet->set) ?>" width="180">
           &nbsp;
        </td>
        <td align="left" class="producttitle">
         	<p><b><?php echo $portlet->name ?></b>
         	<?php echo $portlet->description ?>         
        </td>
   	</tr>
   	<tr>
      	<td>
      		<table width="100%">
				<?php
				foreach($portlet->set as $aSetElement){
					$aSetElement->TTCprice = courseshop_calculate_taxed($aSetElement->price1, $aSetElement->taxcode);
				?>
      			<tr valign="top">
      				<td>
         				<img src="<?php echo $aSetElement->thumb ?>" vspace="10" border="0"><br>
						<?php
					    if ($aSetElement->image != ''){
						?>
				         <a href="Javascript:openImage('<?php echo $aSetElement->image ?>', '<?php echo $_CFG['SITE_URL'] ?>/')"><?php echo get_string('enlarge', 'block_courseshop') ?></a>
						<?php
					    }
						?>
  					</td>
  					<td class="pad10">
						<?php
						if ($aSetElement->showsnameinset){
							echo "<div class=\"producttitle\">$aSetElement->name</div>";
						}
						?>
         				<p><strong><?php print_string('ref', 'block_courseshop') ?> : <?php echo $aSetElement->code ?> - </strong> 
         					<?php print_string('puttc', 'block_courseshop') ?> = <b><?php echo $aSetElement->TTCprice .' '. $portlet->currency ?> </b><br>
						<?php
					    if ($aSetElement->showsdescriptioninset){
							echo $aSetElement->description;
					    }
						?>
         				<input type="button" name="" value="<?php print_string('buy', 'block_courseshop') ?>" onclick="addOneUnit('<?php echo $portlet->shortname ?>', <?php echo $portlet->TTCprice ?>, '<?php echo $portlet->maxdeliveryquant ?>')">
         				<span id="bag_<?php echo $aSetElement->shortname ?>"></span>
      				</td>
   				</tr>
				<?php
				}
				?>
			</table>
		</td>
	</tr>
</table>
