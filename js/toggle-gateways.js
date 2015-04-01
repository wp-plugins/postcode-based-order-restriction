jQuery(function($) { 
    var woo_option = $("select#woocommerce_restrict_gateways_placeorder").val();
    if(woo_option == 'gateways'){
        jQuery("select#woocommerce_specific_allowed_gateways").parent().parent('tr').show();
        jQuery("input#woocommerce_error_gateways").parent().parent('tr').show();
        
        jQuery("input#woocommerce_error_placeorder").parent().parent('tr').hide();
	} else { 
		jQuery("select#woocommerce_specific_allowed_gateways").parent().parent('tr').hide();
        jQuery("input#woocommerce_error_gateways").parent().parent('tr').hide();
        
        jQuery("input#woocommerce_error_placeorder").parent().parent('tr').show();
	}
    $("select#woocommerce_restrict_gateways_placeorder").change(function() {
        if (jQuery(this).val() == "gateways") {
			jQuery("select#woocommerce_specific_allowed_gateways").parent().parent('tr').show();
            jQuery("input#woocommerce_error_gateways").parent().parent('tr').show();
            
            jQuery("input#woocommerce_error_placeorder").parent().parent('tr').hide();
		} else {
			jQuery("select#woocommerce_specific_allowed_gateways").parent().parent('tr').hide();
            jQuery("input#woocommerce_error_gateways").parent().parent('tr').hide();
            
            jQuery("input#woocommerce_error_placeorder").parent().parent('tr').show();
		}
    });
});