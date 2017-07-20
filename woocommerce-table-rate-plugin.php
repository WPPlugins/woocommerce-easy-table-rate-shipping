<?php
/*
Plugin Name: Woocommerce Table Rate Shipping 
Plugin URI: http://www.jem-products.com/plugins.html
Description: Provides shipping for Woocommerce based upon a table of rates. Unlimited countries. 
Version: 1.2.3
Author: JEM Plugins
Author URI: http://www.jem-products.com
*/
 
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
 //lets define some constants
 define('JEM_DOMAIN', 'jem-table-rate-shipping-for-woocommerce');
 define( 'JEM_URL', plugin_dir_url( __FILE__ ) );  // Plugin URL
/**
 * Check if WooCommerce is active
 */
 
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
	function jem_table_rate_init() {
		if ( ! class_exists( 'JEM_Table_Rate_Shipping_Method' ) ) {
			class JEM_Table_Rate_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'jem_table_rate'; 					// Id for your shipping method. Should be uunique.
					$this->method_title       = __( 'Table Rate', JEM_DOMAIN ); 	// Title shown in admin
					$this->method_description =__( 'Table Rate lets you define shipping based on a table of values', JEM_DOMAIN ); // Description shown in admin
 
 					$this->zones_settings 	  = $this->id.'zones_settings';
 					$this->rates_settings 	  = $this->id.'rates_settings';
					$this->enabled            = "yes"; 								// This can be added as an setting but for this example its forced enabled
					$this->title              = "Table Rate Shipping"; 				// This can be added as an setting but for this example its forced.
 
 					$this->option_key  		  = $this->id.'_table_rates';			//The key for wordpress options
					$this->options			  = array();									//the actual tabel rate options saved 
 					$this->condition_array	= array();									//holds an array of CONDITIONS for the select
					$this->country_array	= array();									//holds an array of COUNTRIES for the select
 					$this->counter			= 0;									//we use this to keep unique names for the rows
					$this->init();
					
					$this->get_options();											//load the options
				}
 

				
				
				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API
					$this->init_form_fields(); 	// This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); 	// This is part of the settings API. Loads settings you previously init.
 					
					$this->title = $this->settings['title'];
 					//set up the select arrays
					$this->create_select_arrays();
					
					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
					//And save our options
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_custom_settings' ) );
					
				}
				
				/**
				** This initialises the form field
				*/
				function init_form_fields(){
					
					$this->form_fields = array(
	                    'enabled' => array(
                            'title' 		=> __( 'Enable/Disable', JEM_DOMAIN ),
                            'type' 			=> 'checkbox',
                            'label' 		=> __( 'Enable this shipping method', JEM_DOMAIN ),
                            'default' 		=> 'no'
						),
	                    'title' => array(
                            'title' 		=> __( 'Checkout Title', JEM_DOMAIN ),
                            'description' 		=> __( 'This controls the title which the user sees during checkout.', JEM_DOMAIN ),
                            'type' 			=> 'text',
                            'default' 		=> 'Table Rate Shipping',
                            'desc_tip'     => true
						),
	                    'handling_fee' => array(
                            'title' 		=> __( 'Handling Fee', JEM_DOMAIN ),
                            'description' 		=> __( 'Enter an amount for the handling fee - leave BLANK to disable.', JEM_DOMAIN ),
                            'type' 			=> 'text',
                            'default' 		=> ''
						),
						'tax_status' => array(
							'title' 		=> __( 'Tax Status', JEM_DOMAIN ),
							'type' 			=> 'select',
							'default' 		=> 'taxable',
							'options'		=> array(
								'taxable' 	=> __( 'Taxable', JEM_DOMAIN ),
								'notax' 		=> __( 'Not Taxable', JEM_DOMAIN ),
							)
						),
						'table_rates_table' => array(
							'type'				=> 'table_rates_table'
						)							
					);
				
				}
				

				/**
				* admin_options
				* These generates the HTML for all the options
				*/
				function admin_options() {
				?>
				
						
						
						
						
					<style>
						
						.jem-zone-row{
							background-color: #837E7E !important;
						}
						.jem-show-row{
						    display: table-row;
						    background-color: #0073aa;
						}
					</style>
					<h2><?php _e('Table Rate Shipping Options','woocommerce'); ?></h2>
					 <table class="form-table">
					 <?php $this->generate_settings_html(); ?>
					 </table> 
				<?php					
				}
				
				
				/**
				 * Returns the latest counter 
				*/
				function get_counter(){
					$this->counter = $this->counter + 1;
					return $this->counter;	
				}
				
				
				/**
				 * Generates HTML for table_rate settings table.
				 * this gets called automagically!
				 */
				function generate_table_rates_table_html() {
					ob_start();
					
					
					
					//OK lets pump out some rows to see how it goes!!!
					// we put the jscript stuff at the top
					?>
					
				<!--  begin email -->

<!-- Begin MailChimp Signup Form -->
<link href="//cdn-images.mailchimp.com/embedcode/classic-081711.css" rel="stylesheet" type="text/css">
<style type="text/css">
	#mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
	/* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
	   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
	   #optin {
	background: #dde2ec;
	border: 2px solid #1c3b7e;
	/* padding: 20px 15px; */
	text-align: center;
	width: 800px;
}
	#optin input {
		background: #fff;
		border: 1px solid #ccc;
		font-size: 15px;
		margin-bottom: 10px;
		padding: 8px 10px;
		border-radius: 3px;
		-moz-border-radius: 3px;
		-webkit-border-radius: 3px;
		box-shadow: 0 2px 2px #ddd;
		-moz-box-shadow: 0 2px 2px #ddd;
		-webkit-box-shadow: 0 2px 2px #ddd
	}
		#optin input.name { background: #fff url('<?php echo JEM_URL; ?>/images/name.png') no-repeat 10px center; padding-left: 35px }
		#optin input.myemail { background: #fff url('<?php echo JEM_URL; ?>/images/email.png') no-repeat 10px center; padding-left: 35px }
		#optin button {
			background: #217b30 url('<?php echo JEM_URL; ?>/images/green.png') repeat-x top;
			border: 1px solid #137725;
			color: #fff;
			cursor: pointer;
			font-size: 14px;
			font-weight: bold;
			padding: 2px 0;
			text-shadow: -1px -1px #1c5d28;
			width: 120px;
			height: 38px;
		}
			#optin button:hover { color: #c6ffd1 }
		.optin-header{
			font-size: 24px;
			color: #ffffff;
			background-color: #1c3b7e;
			padding: 20px 15px;
		}
		#jem-submit-results{
			padding: 10px 0px;
			font-size: 24px;
		}
</style>
<div id="optin">

    <div id="mc_embed_signup_scroll">
	<div class="optin-header">Upgrade to Pro - get a 20% Discount Coupon</div>
<div class="mc-field-group" style="padding: 20px 15px;; text-align: left;">
	<input type="text" value="Enter your email" size="30" name="EMAIL" class="myemail" id="mce-EMAIL" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;"
	>
	<input type="text" value="Enter your name" size="30" name="FNAME" class="name" id="mce-FNAME" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;"
	>
	<button id="mc_button" class="button" >Get Discount</button>
	</div>
	<div id="mce-responses" class="clear">
		<div class="response" id="mce-error-response" style="display:none"></div>
		<div class="response" id="mce-success-response" style="display:none"></div>
	</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_6d531bf4acbb9df72cd2e718d_de987ac678" tabindex="-1" value=""></div>
    <div class="clear"><img src="<?php echo JEM_URL ?>/images/lock.png">We respect your privacy and will never sell or rent your details</div>
    <div id="jem-submit-results"></div>
    </div>
</div>
<script>
	jQuery("#mc_button").click(function(e){
		e.preventDefault();
		console.log('clicked');
		data = {};
			
		data["EMAIL"] = jQuery("#mce-EMAIL").val();
		data["NAME"] = jQuery("#mce-FNAME").val();
			
		 jQuery.ajax({
		        url: '//jem-products.us12.list-manage.com/subscribe/post-json?u=6d531bf4acbb9df72cd2e718d&amp;id=de987ac678&c=?',
		        type: 'post',
		        data: data,
		        dataType: 'json',
		        contentType: "application/json; charset=utf-8",
		        success: function (data) {
		           if (data['result'] != "success") {
		                //ERROR
		                console.log("error");
		                console.log(data['msg']);
		           } else {
		                //SUCCESS - Do what you like here
		                jQuery("#jem-submit-results").text("Please Check Your Email for your Code");
		           }
		        }
		    });
		
	});

</script>
				
				<!--  end email -->

					
					
					
					<tr>
						<th scope="row" class="titledesc"><?php _e( 'Table Rates', JEM_DOMAIN ); ?></th>
						<td id="<?php echo $this->id; ?>_settings">
							<table class="shippingrows widefat">
						        <col style="width:0%">
						        <col style="width:0%">
						        <col style="width:0%">
						        <col style="width:100%;">
								<thead>
									<tr>
										<th class="check-column"></th>
										<th>Shipping Zone Name</th>
										<th>Condition</th>
										<th>Countries</th>
									</tr>
								</thead>
								<tbody style="border: 1px solid black;">
									<tr style="border: 1px solid black;">
										<td colspan="5" class="add-zone-buttons">
											<a href="#" class="add button">Add New Shipping Zone</a>
											<a href="#" class="delete button">Delete Selected Zones</a>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<script>
				   		var options = <?php echo json_encode($this->create_dropdown_options()); ?>;

						var country_array = <?php echo json_encode($this->country_array); ?>;
						var condition_array = <?php echo json_encode($this->condition_array); ?>;
						var pluginID = <?php echo json_encode($this->id); ?>;

						var lastID = 0;

							<?php
								
									foreach($this->options as $key => $value){
										
										//add the key back into the json object
										$value['key'] = $key;
										$row = json_encode($value);
										echo "jQuery('#{$this->id}_settings table tbody tr:last').before(create_zone_row({$row}));\n";
									}
									

						   	?>
							   

						
						/**
						* This creates a new ZONE row
						*/
						function create_zone_row(row){
							//lets get the ID of the last one
							
							var el = '#' + pluginID + '_settings .jem-zone-row';
							lastID = jQuery(el).last().attr('id');
							
							//Handle no rows
							if(typeof lastID == 'undefined' || lastID == ""){
								lastID =1;
							} else {
								lastID = Number(lastID) + 1;
							}
							
							var html = '\
									<tr id="' + lastID + '" class="jem-zone-row" >\
										<input type="hidden" value="' + lastID + '" name="key[' + lastID + ']"></input>\
										<td><input type="checkbox" class="jem-zone-checkbox"></input></span></td>\
										<td><input type="text" size="30" value="' + row["key"] +'"  name="zone-name[' + lastID + ']"/></td>\
										<td>\
											<select name="condition[' + lastID + ']">\
											' + generate_condition_html(row.condition) +'\
											</select>\
										</td>\
										<td>\
											<select multiple="multiple" class="multiselect chosen_select" name="countries[' + lastID + '][]">\
											' + generate_country_html(row.countries) + '\
													</select>\
										</td>\
									</tr>\
							';	
							
							//This is the expandable/collapsable row for that holds the rates
							html += '\
								<tr class="jem-rate-holder">\
									<td colspan="1">\
									</td>\
									<td colspan="3">\
										<table class="jem-rate-table shippingrows widefat" id="' + lastID + '_rates">\
											<thead>\
												<tr>\
													<th></th>\
													<th style="width: 30%">Min Value</th>\
													<th style="width: 30%">Max Value</th>\
													<th style="width: 40%">Shipping Rate</th>\
												</tr>\
											</thead>\
											' + create_rate_row(lastID, row) +'\
											<tr>\
												<td colspan="4" class="add-rate-buttons">\
													<a href="#" class="add button" name="key_' + lastID + '">Add New Rate</a>\
													<a href="#" class="delete button">Delete Selected Rates</a>\
												</td>\
											</tr>\
										</table>\
									</td>\
								</tr>\
							';
							
							return html;
						}

						/**
						* This creates a new RATE row
						* The container Table is passed in and this row is added to it
						*/
						function create_rate_row(lastID, row){
							
							
							if(row == null || row.rates.length == 0){
								//lets manufacture a rows
								//create dummy row
								var row = {};
								row.key = "";
								row.condition = [""];
								row.countries = [];
								row.rates = [];
								row.rates.push([]);
								row.rates[0].min = "";
								row.rates[0].max = "";
								row.rates[0].shipping = "";
								}
							//loop thru all the rate data and create rows
							
							//handles if there are no rate rows yet
							if(typeof(row.min) == 'undefined' || row.min==null){
								row.min=[];
							}
							
							var html = '';
							for(var i=0; i<row.rates.length; i++){
								html += '\
									<tr>\
										<td>\
											<input type="checkbox" class="jem-rate-checkbox" id="' + lastID + '"></input>\
										</td>\
										<td>\
											<input type="text" size="20" placeholder="" name="min[' + lastID + '][]" value="' + row.rates[i].min + '"></input>\
										</td>\
										<td>\
											<input type="text" size="20" placeholder="" name="max[' + lastID + '][]" value="' + row.rates[i].max + '"></input>\
										</td>\
										<td>\
											<input type="text" size="10" placeholder="" name="shipping[' + lastID + '][]" value="' + row.rates[i].shipping + '"></input>\
										</td>\
									</tr>\
								';
							
								
								
							}
							
							
							return html;
						}	
					
						/**
						 * Handles the expansion contraction of the rate table for the zone
						 */	
						function expand_contract(){
					
							var row = jQuery(this).parent('td').parent('tr').next();
							
							if(jQuery(row).hasClass('jem-hidden-row')){
								jQuery(row).removeClass('jem-hidden-row').addClass('jem-show-row');	
								jQuery(this).removeClass('expand-icon').addClass('collapse-icon');							
							} else {
								jQuery(row).removeClass('jem-show-row').addClass('jem-hidden-row');																
								jQuery(this).removeClass('collapse-icon').addClass('expand-icon');							
							}
							
							
							
						}
						
						
						//**************************************
						// Generates the HTML for the country
						// select. Uses an array of keys to
						// determine which ones are selected
						//**************************************
						function generate_country_html(keys){
											
							html = "";
							
							for(var key in country_array){
								
								if(keys.indexOf(key) != -1){
									//we have a match
									html += '<option value="' + key + '" selected="selected">' + country_array[key] + '</option>'; 
								} else {
									html += '<option value="' + key + '">' + country_array[key] + '</option>'; 
									
								}
							}
							
							return html;
						}
						
						
						//**************************************
						// Generates the HTML for the CONDITION
						// select. Uses an array of keys to
						// determine which ones are selected
						//**************************************
						function generate_condition_html(keys){
											
							html = "";
							
							for(var key in condition_array){
								
								if(keys.indexOf(key) != -1){
									//we have a match
									html += '<option value="' + key + '" selected="selected">' + condition_array[key] + '</option>'; 
								} else {
									html += '<option value="' + key + '">' + condition_array[key] + '</option>'; 
									
								}
							}
							
							return html;
						}
						
						//***************************
						// Handle add/delete clicks
						//***************************

						//ZONE TABLE
						
						
						/*
						* add new ZONE row
						*/
						var zoneID = "#" + pluginID + "_settings";

						jQuery(zoneID).on('click', '.add-zone-buttons a.add', function(){
							
							//ok lets add a row!
							
							
							var id = "#" + pluginID + "_settings table tbody tr:last";
							//create empty row
							var row = {};
							row.key = "";
							row.min = [];
							row.rates = [];
							row.condition = [];
							row.countries = [];
							jQuery(id).before(create_zone_row(row));

							//turn on select2 for our row
							if (jQuery().chosen) {
								jQuery("select.chosen_select").chosen({
									width: '350px',
									disable_search_threshold: 5
								});
							} else {
								jQuery("select.chosen_select").select2();
							}
							
							
							return false;
						});
						
						/**
						 * Delete ZONE row
						 */
						jQuery(zoneID).on('click', '.add-zone-buttons a.delete', function(){
		
							//loop thru and see what is checked - if it is zap it!
							var rowsToDelete = jQuery(this).closest('table').find('.jem-zone-checkbox:checked');
							
							jQuery.each(rowsToDelete, function(){
								
								var thisRow = jQuery(this).closest('tr');
								//first lets get the next sibl;ing to this row
								var nextRow = jQuery(thisRow).next();
								
								//it should be a rate row
								if(jQuery(nextRow).hasClass('jem-rate-holder')){
									//remove it!
									jQuery(nextRow).remove();
								} else {
									//trouble at mill
									return;
								}
								
								jQuery(thisRow).remove();
							});
							
							//TODO - need to delete associated RATES
														
							return false;
						});
						
												
						//RATE TABLES
						
						/**
						* ADD RATE BUTTON
						*/
						jQuery(zoneID).on('click', '.add-rate-buttons a.add', function(){
							
							//we need to get the key of this zone - it's in the name of of the button
							var name = jQuery(this).attr('name');
							name = name.substring(4);
							
							//remove key_ 
							//ok lets add a row!
							

							var row = create_rate_row(name, null);
							jQuery(this).closest('tr').before(row);
							
							return false;
						});
					
						/**
						 * Delete RATE roe
						 */	
						jQuery(zoneID).on('click', '.add-rate-buttons a.delete', function(){

							//loop thru and see what is checked - if it is zap it!
							var rowsToDelete = jQuery(this).closest('table').find('.jem-rate-checkbox:checked');
							
							jQuery.each(rowsToDelete, function(){
								jQuery(this).closest('tr').remove();
							});
							
														
							return false;
						});
						
						//These handle building the select arras
						
						
							<?php
						   			echo "jQuery('#{$this->id}_settings').on('click', '.jem-expansion', expand_contract) ;\n";

						   	?>
					</script>
					
					<?php
					return ob_get_clean();		
				}


				//*********************
				// PHP functions
				//***********************

				function create_select_arrays(){
					
					//first the CONDITION html
					$this->condition_array = array();
					$this->condition_array['weight'] = sprintf(__( 'Weight (%s)', 'MHTR_DOMAIN' ), get_option('woocommerce_weight_unit'));
			   		$this->condition_array['total'] = sprintf(__( 'Total Price (%s)', 'MHTR_DOMAIN' ), get_woocommerce_currency_symbol());
					

					//Now the countries
					$this->country_array = array();
					
			   		// Get the country list from Woo....
					foreach (WC()->countries->get_shipping_countries() as $id => $value) :
		   				$this->country_array[esc_attr($id)] = esc_js($value);
					endforeach;							
				}


				//TODO - do we need this function?
 				/**				 
				* This generates the select option HTML for teh zones & rates tables
				*/
				function create_select_html(){
					//first the CONDITION html
					$arr = array();
					$arr['weight'] = sprintf(__( 'Weight (%s)', 'MHTR_DOMAIN' ), get_option('woocommerce_weight_unit'));
			   		$arr['total'] = sprintf(__( 'Total Price (%s)', 'MHTR_DOMAIN' ), get_woocommerce_currency_symbol());
					   
					//now create the html from the array
					$html= '';
					foreach ($arr as $key => $value) {
						$html .= '<option value=">' . $key . '">' . $value . '</option>'; 
					}
					
					$this->condition_html = $html;
					
					$html = '';
					$arr = array();
					//Now the countries
					
			   		// Get the country list from Woo....
					foreach (WC()->countries->get_shipping_countries() as $id => $value) :
		   				$arr[esc_attr($id)] = esc_js($value);
					endforeach;							
					
					//And create the HTML
					foreach ($arr as $key => $value) {
						$html .= '<option value=">' . $key . '">' . $value . '</option>'; 
					}

					$this->country_html = $html;					
					
				}				


				//Creates the HTML options for the selected
				
				function create_dropdown_html($arr){
					
					$arr = array();
					

					
					$this->condition_html = html;
				}
										
				/**
				 * Create dropdown options 
				 */
				function create_dropdown_options() {
				
					$options = array();
				
		   		
			   		// Get the country list from Woo....
					foreach (WC()->countries->get_shipping_countries() as $id => $value) :
		   				$options['country'][esc_attr($id)] = esc_js($value);
					endforeach;
					
					// Now the conditions - cater for language & woo
			   		$option['condition']['weight'] = sprintf(__( 'Weight (%s)', 'JEM_DOMAIN' ), get_option('woocommerce_weight_unit'));
			   		$option['condition']['price'] = sprintf(__( 'Total (%s)', 'JEM_DOMAIN' ), get_woocommerce_currency_symbol());
			   		
			   		return $options;
				}	

				
				/**
				* This saves all of our custom table settings
				*/
				function process_custom_settings() {
					
					//Arrays to hold the clean POST vars
					$keys =array();
					$zone_name =array();
					$condition = array();
					$countries = array();
					$min = array();
					$max = array();
					$shipping = array();
					
					
					//Take the POST vars, clean em up and put thme in nice arrays 
					if ( isset( $_POST[ 'key'] ) ) $keys = array_map( 'wc_clean', $_POST['key'] );
					if ( isset( $_POST[ 'zone-name'] ) ) $zone_name = array_map( 'wc_clean', $_POST['zone-name'] );
					if ( isset( $_POST[ 'condition'] ) ) $condition = array_map( 'wc_clean', $_POST['condition'] );
					//no wc_clean as multi-D arrays
					if ( isset( $_POST[ 'countries'] ) ) $countries = $_POST['countries'] ;
					if ( isset( $_POST[ 'min'] ) ) $min = $_POST['min'] ;
					if ( isset( $_POST[ 'max'] ) ) $max = $_POST['max'] ;
					if ( isset( $_POST[ 'shipping'] ) ) $shipping = $_POST['shipping'] ;

					//todo - need to add soem validation here and some error messages???
					
				
					
					//Master var of options - we keep it in one big bad boy
					$options = array();
					
					//OK we need to loop thru all of them - the keys will help us here - process by key
					foreach($keys as $key => $value){
						
						
						//we only process it if all the fields are set
						if(
							empty($zone_name[$key]) ||
							empty($condition[ $key ]) ||
							empty($countries[ $key ])
							){
							//something is empty so don't save it
							continue;
							
						}
						
						
						//Get the zone name - this is our main key
						$name =  $zone_name[$key];
						
						
						//Going to add the rates now.
						//before we do that check if we have any empty rows and delete them
						$obj =array();
						foreach ($min[ $key ] as $k => $val) {
    							if(
									empty($min[ $key ][$k]) &&
									empty($max[ $key ][$k]) &&
									empty($shipping[ $key][$k]) 
								)
								{
									unset($min[ $key ][$k]);
									unset($max[ $key ][$k]);
									unset($shipping[ $key ][$k]);
								}
								else {
									//add it to the object array
									$obj[] = array("min" => $min[ $key ][ $k], "max" => $max[ $key ][ $k], "shipping" => $shipping[ $key ][ $k]);									
								}
									
						}		
						
						//OK now lets sort or array of objects!!
						usort($obj, 'self::cmp');
						
						//create the array to hold the data				
						$options[ $name ] = array();
						
						$options[ $name ][ 'condition'] = $condition[ $key ]; 
						$options[ $name ][ 'countries'] = $countries[ $key ]; 
						$options[ $name ][ 'min'] = $min[ $key ]; 
						$options[ $name ][ 'max'] = $max[ $key ]; 
						$options[ $name ][ 'shipping'] = $shipping[ $key ]; 
						$options[ $name ][ 'rates'] = $obj;			//This is the sorted rates object!
						


						
					}
					
					
					
					//SAVE IT
					update_option( $this->option_key, $options ); 
				}	
				
				//Comparision function for usort of associative arrays
				function cmp($a, $b){
					return $a['min'] - $b['min'];
				}
				
				/**
				* This RETIEVES  all of our custom table settings
				
				*/
				function get_options() {
					
					//Retrieve the zones & rates
					$this->options = array_filter( (array) get_option( $this->option_key ) );
					
					$x = 5;
				}	
				
				/**
				 * calculate_shipping function. Woo calls this automagically
				 *
				 */
				public function calculate_shipping( $package = Array() ) {
					
					
					//what is the tax status
					if ($this->settings['tax_status'] == 'notax') {
						$taxes = false;
					} else {
						$taxes = '';
					}
					
					//test just to get a value out there....
					$cost= 1;
					
					//ok first lets get the country that this order is for
					$dest_country = $package['destination']['country'];
	
					//now lets get the rates associated with this country
					$rates = $this->get_rates_for_country($dest_country);
					
					if($rates == null){
						//there is nothing available for this country so just return
						return;
						
					}

					//OK we have valid rates for the country, now lets find the appropriate entry....
					//We assume the user entered the rates ocrrectly..


					if($rates[0]['condition'] == 'total'){
						//price based comparisons
						$cost = $this->find_matching_rate(WC()->cart->cart_contents_total, $rates);
					} else {
						//weight based comparison
						$cost = $this->find_matching_rate(WC()->cart->cart_contents_weight, $rates);
					}
					
					//if we got nothing back then there is no match in this case, we just return without an object and
					// this option disapears
				
					if($cost==null){
						return;		
					}
					
					//add on the handling fee!
					
					//chek it is valid first
					if(is_numeric($this->settings['handling_fee'])){
						$cost += $this->settings['handling_fee'];			
					}
					
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $cost,
						'taxes' => $taxes,
						'calc_tax' => 'per_order'
					);

					// Register the rate
					$this->add_rate( $rate );
				}
				
				function get_rates_for_country($country){
					
					//Loop thru and see if we can find one
					$ret = array();
					
					foreach($this->options as $rate){
						if(in_array($country, $rate['countries'])){
							$ret[] =  $rate;
						}
						
					}
					
					//if we found something return it, otherwise a null.
					if(count($ret) >0){
						return $ret;
					} else {
						return null; 
					}

				}	
				
				
				//Here we find the matching rate
				function find_matching_rate($value, $zones){
					foreach($zones as $zone){
						//inside each zone will be the arrays of min max & shipping
						//TODO - should probably make this a better data structure - array of objects, next version
						
						
						// remember * means infinity!
						for($i=0; $i<count($zone['max']); $i++){


							if($zone['max'][$i] == '*'){
								if($value >= $zone['min'][$i]){
									return $zone['shipping'][$i];
								}
							} else {
								if($value >= $zone['min'][$i] && $value <= $zone['max'][$i]){
									return $zone['shipping'][$i];
								}
								
								
							}
							
						}
						
						//OK if we got all the way to here, then we have NO match
						return null;
					}
				}		
	
			}
		}
	}
 
	add_action( 'woocommerce_shipping_init', 'jem_table_rate_init' );
 
	function add_jem_table_rate( $methods ) {
		$methods[] = 'JEM_Table_Rate_Shipping_Method';
		return $methods;
	}
 
	add_filter( 'woocommerce_shipping_methods', 'add_jem_table_rate' );
}


/**
 * Load admin scripts
 */
function jem_table_rate_admin_scripts( $hook ) {
    global $wptr_settings_page, $post_type;

    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	

	//Load the styles & scripts we need 
    if ($hook == 'woocommerce_page_wc-settings') {

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );

    }
}

add_action( 'admin_enqueue_scripts', 'jem_table_rate_admin_scripts', 100 );








