<?php
/**
 * Plugin Name: Postcode Based Order Restriction
 * Description: This WooCommerce plugin <strong>enables order restriction based on specific zip/post codes</strong>. You have to enter list of postcode in given area for which the order restriction works. When activated you will restrict selected gateways or billing address, shipping address and both address at checkout step based on specific zip/post codes.  
 * Author: PrecursorWeb
 * Author URI: http://precursorweb.com/
 * Plugin URI: http://precursorweb.com/
 * Version: 1.0
 * Contributors: PrecursorWeb
 * Requires at least: 4.0
 * Tested up to: 4.1.1
 *
 * You should have received a copy of the GNU General Public License
 * License: GPL version 2 or later - <http://www.gnu.org/licenses/>.
 */

add_action('admin_enqueue_scripts', 'toggle_gateways_field');
function toggle_gateways_field() {    
    wp_enqueue_script( 'woocommerce_admin_head', plugins_url( 'js/toggle-gateways.js', __FILE__ ), array('jquery') );
}

function add_order_number_start_setting( $settings ) {
    
    global $woocommerce;
    $updated_settings = array();
    
    foreach ( $settings as $section ) {
        /**
         * Add section at the bottom of the general options
         */
        if ( isset( $section['id'] ) && 'general_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) {

            $updated_settings[] = array( 'type' => 'sectionend', 'id' => 'general_options'); // end the general options section
            
            /**
             * Start new section "Postcode Based Order Restriction".
             */     
            $updated_settings[] = array( 
                'title' => __( 'Postcode Based Order Restriction', 'woocommerce' ), 
                'type' => 'title', 'desc' => '', 
                'id' => 'postcode_order_restriction' 
            );    
            
            /**
             * Enable postcode based order restriction
             */     
            $updated_settings[] = array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'desc'    => __( 'Enable Postcode Based Order Restriction', 'woocommerce' ),
                'id'      => 'woocommerce_postcode_order_restriction_enabled',
                'type'    => 'checkbox',
                'default' => 'No',
            );
            
            /**
             * Restriction mode either allow or restrict
             */ 
            $updated_settings[] = array(
                'title'    => __( 'Restriction Mode', 'woocommerce' ),
                'desc'     => __( 'Base on this option below zip codes are allowed/restricted at checkout. <br/>
                                   Allow - Allow specific postcode for buy.<br/>
                                   Restric - Restrict specific postcode for buy.', 'woocommerce' ),
                'id'       => 'woocommerce_allow_restrict',
                'type'     => 'select',
                'class'    => 'chosen_select',
                'css'      => 'min-width: 350px;',
                'desc_tip' =>  true,
                'default'  => 'allow',
                'options'  => array(
                    'allow'   => __( 'Allow', 'woocommerce' ),
                    'restrict' => __( 'Restrict', 'woocommerce' )
                )
            );
            
            /**
             * Restriction mode for billing or shipping
             */ 
            $updated_settings[] = array(
                'title'    => __( 'Default Order Restriction', 'woocommerce' ),
                'desc'     => __( 'Enable restriction base on postcodes at checkout.<br/>
                                   Billing - Order restriction apply for billing detail.<br/>
                                   Shipping - Order restriction apply for shipping detail.<br/>
                                   Both - Order restriction apply for billing & shipping details.', 'woocommerce' ),
                'id'       => 'woocommerce_restrict_option',
                'type'     => 'select',
                'class'    => 'chosen_select',
                'css'      => 'min-width: 350px;',
                'desc_tip' =>  true,
                'default'  => 'billing',
                'options'  => array(
                    'billing'  => __( 'Billing', 'woocommerce' ),
                    'shipping' => __( 'Shipping', 'woocommerce' ),
                    'both'     => __( 'Both', 'woocommerce' )
                )
            );   
            
            /**
             * Zip/Post codes
             */
            $updated_settings[] = array(
                'title'   => __( 'Zip/Post Codes', 'woocommerce' ),
                'id'      => 'woocommerce_postcode_order',
                'css'     => 'width:100%; height: 65px;',
                'type'    => 'textarea',
                'desc'    => 'Please enter valid postcode with comma like 12345, 56789 etc',
                'desc_tip'=> true
            );  
            
            /**
             * Restrict through either disable place order or gateways
             */ 
             $updated_settings[] = array(
                'title'    => __( 'Restrict Through', 'woocommerce' ),
                'desc'     => __( 'Disable Place Order - Place order button will disable at checkout base on postcode restriction.<br/>
                                   Gateway - Selected Gataways will disable at checkout base on postcode restriction.', 'woocommerce' ),
                'id'       => 'woocommerce_restrict_gateways_placeorder',
                'type'     => 'select',
                'class'    => 'chosen_select',
                'css'      => 'min-width: 350px;',
                'desc_tip' =>  true,
                'default'  => 'gateways',
                'options'  => array(
                    'placeorder' => __( 'Disable Place Order', 'woocommerce' ),
                    'gateways'   => __( 'Gateways', 'woocommerce' )
                )
            );  
            
            /**
             * Error message for disable place order
             */
            $updated_settings[] = array(
                'title'   => __( 'Error Message', 'woocommerce' ),
                'id'      => 'woocommerce_error_placeorder',
                'css'     => 'width:94%;',
                'type'    => 'text',
                'default' => 'Sorry, Currently we are not providing service for provided zipcode.',
                'desc'    => 'Optional',
                'desc_tip'=> false
            );                
            
            /**
             * Multiselect available gateways
             */
            $_available_gateways = array();
            foreach($woocommerce->payment_gateways->payment_gateways as $key => $gateways):
                if ($gateways->is_available()):
                    $_available_gateways[$gateways->id] = __($gateways->title,'woocommerce');
                endif;
            endforeach;           
            
            $updated_settings[] = array(
                'title'   => __('Restrict Available Gateways','woocommerce'),
                'desc'    => 'This option lets you limit available gateways for specific postcode in checkout.',
                'id'      => 'woocommerce_specific_allowed_gateways',
                'css'     => 'min-width: 350px;',
                'default' => '',
                'desc_tip'=> true,
                'type'    => 'multiselect',
                'options' => $_available_gateways
            );
            
            /**
             * Error message for gateways
             */
            $updated_settings[] = array(
                'title'   => __( 'Error Message', 'woocommerce' ),
                'id'      => 'woocommerce_error_gateways',
                'css'     => 'width:94%;',
                'type'    => 'text',
                'default' => 'Sorry, Currently %s payments methods are disable for provided zipcode.',
                'desc'    => 'Optional',
                'desc_tip'=> false
            );  
        }
        $updated_settings[] = $section;
    }
    return $updated_settings;
}
add_filter( 'woocommerce_general_settings', 'add_order_number_start_setting' );

function postcode_based_payment_gateways( $methods ) { 
    if(defined('WOOCOMMERCE_CHECKOUT') == 1){ 
        global $woocommerce; 
        
        $postcode_order_restriction_enabled = get_option( 'woocommerce_postcode_order_restriction_enabled' );
        if($postcode_order_restriction_enabled == 'yes'){ // is enabled
            
            if($_available_gateways = (array)$woocommerce->payment_gateways->payment_gateways){                
                
                $woocommerce_restrict_by = get_option( 'woocommerce_restrict_gateways_placeorder' );
                if($woocommerce_restrict_by == 'gateways'){ $all_postcode = '';
                    
                    $woocommerce_postcode_order = get_option( 'woocommerce_postcode_order' ); // get list of all zip/postcode
                    $all_postcode = array_map('trim', explode(",", $woocommerce_postcode_order));
                    
                    if(count($all_postcode)){ $woocommerce_specific_allowed_gateways = ''; $woocommerce_error_gateways = '';
                        
                        $woocommerce_allow_restrict = get_option( 'woocommerce_allow_restrict' ); // get restriction mode
                        $woocommerce_restrict_option = get_option( 'woocommerce_restrict_option' ); // get restriction option
                        
                        $woocommerce_specific_allowed_gateways = get_option( 'woocommerce_specific_allowed_gateways' ); // get available selected gateways 
                        $woocommerce_error_gateways = get_option( 'woocommerce_error_gateways' ); // get error message   
                        if(!$woocommerce_error_gateways && $woocommerce_error_gateways == ''){
                            $woocommerce_error_gateways = 'Sorry, Currently %s payments methods are disable for provided zipcode.';
                        }        
                    
                        if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'billing'){ $sel_gateways_title = array(); 
                            if (!in_array(trim($woocommerce->customer->postcode), $all_postcode)) { 
                                                                    
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );                                 
                            }
                        } else if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'shipping'){ $sel_gateways_title = array(); 
                            if (!in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                                                                    
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );                                 
                            }
                        } else if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'both'){ $sel_gateways_title = array(); 
                            if (!in_array(trim($woocommerce->customer->postcode), $all_postcode) || !in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                                                                    
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );                                 
                            } 
                        } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'billing'){ $sel_gateways_title = array();
                            if (in_array(trim($woocommerce->customer->postcode), $all_postcode)) { 
                                
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );    
                            }
                        } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'shipping'){ $sel_gateways_title = array();
                            if (in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                                
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );    
                            }                           
                        } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'both'){ $sel_gateways_title = array();
                            if (in_array(trim($woocommerce->customer->postcode), $all_postcode) || in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                                
                                foreach($_available_gateways as $gateways): 
                                    if (in_array($gateways->id, $woocommerce_specific_allowed_gateways)) {    
                                        unset( $methods[array_search(get_class($gateways),$methods)] );
                                        array_push($sel_gateways_title,$gateways->title);
                                    }
                                endforeach;
                                    
                                wc_add_notice( sprintf( __( $woocommerce_error_gateways, 'woocommerce' ), implode(" ,",$sel_gateways_title) ), 'error' );    
                            }
                        } 
                    }
                }
            }
        }  
    }  
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'postcode_based_payment_gateways' );

function postcode_based_order_button_html() { 
    global $woocommerce;
    
    $order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
    $pl_btn = '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" 
                      value="'.esc_attr( $order_button_text ).'" data-value="'.esc_attr( $order_button_text ).'" />';
                              
    $postcode_order_restriction_enabled = get_option( 'woocommerce_postcode_order_restriction_enabled' );
    if($postcode_order_restriction_enabled == 'yes'){ // is enabled 
        
        $woocommerce_restrict_by = get_option( 'woocommerce_restrict_gateways_placeorder' );
        if($woocommerce_restrict_by == 'placeorder'){ $all_postcode = '';
            
            $woocommerce_postcode_order = get_option( 'woocommerce_postcode_order' ); // get list of all zip/postcode
            $all_postcode = array_map('trim', explode(",", $woocommerce_postcode_order));       
                                             
            if(count($all_postcode)){ $notallow_pl_btn = '';
                
                $woocommerce_allow_restrict = get_option( 'woocommerce_allow_restrict' ); // get restriction mode
                $woocommerce_restrict_option = get_option( 'woocommerce_restrict_option' ); // get restriction option
                
                $woocommerce_error_placeorder = get_option( 'woocommerce_error_placeorder' ); // error message
                if(!$woocommerce_error_placeorder && $woocommerce_error_placeorder == ''){
                    $notallow_pl_btn = '<ul class="woocommerce-error"><li>'. __('Sorry, Currently we are not providing service for provided zipcode.', 'woocommerce') .'</li></ul>';
                } else {
                    $notallow_pl_btn = '<ul class="woocommerce-error"><li>'. __($woocommerce_error_placeorder, 'woocommerce').'</li></ul>';   
                }
            
                if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'billing'){ 
                    if (!in_array(trim($woocommerce->customer->postcode), $all_postcode)) { 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    } 
                } else if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'shipping'){ 
                    if (!in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    }  
                } else if($woocommerce_allow_restrict == 'allow' && $woocommerce_restrict_option == 'both'){ 
                    if (!in_array(trim($woocommerce->customer->postcode), $all_postcode) || !in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) { 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    }                    
                } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'billing'){ 
                    if (in_array(trim($woocommerce->customer->postcode), $all_postcode)) {                 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    }
                } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'shipping'){ 
                    if (in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) {                 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    }
                } else if($woocommerce_allow_restrict == 'restrict' && $woocommerce_restrict_option == 'both'){ 
                    if (in_array(trim($woocommerce->customer->postcode), $all_postcode) || in_array(trim($woocommerce->customer->shipping_postcode), $all_postcode)) {                 
                        return $notallow_pl_btn;
                    } else {
                        return $pl_btn;
                    }
                } else {
                    return $pl_btn;
                }  
            }
        } else {        
            return $pl_btn;
        }
    } else {        
        return $pl_btn;
    }
}
add_filter( 'woocommerce_order_button_html', 'postcode_based_order_button_html' );

?>