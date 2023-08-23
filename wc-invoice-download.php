<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       WC Invoice Download
 * Plugin URI:        https://wordpress.org/plugins/wc-invoice-download 
 * Description:       Download PDF invoices for WooCommerce orders using Dompdf.
 * Version:           1.0.0
 * Author:            Rakesh
 * Author URI:        https://github.com/aryanbokde
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-invoice
 * Domain Path:       /languages
 *  
 * @link              https://github.com/aryanbokde
 * @since             1.0.0
 * @package           wc-invoice
 *
 */


defined( 'ABSPATH' ) || exit;

require_once(plugin_dir_path(__FILE__). 'vendor/autoload.php');

// Use Dompdf to generate a PDF
use Dompdf\Dompdf;
use Dompdf\Options;


function wc_invoice_check_others_plugins_loaded(){
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function(){
          
            echo '<div class="error"><p><strong>'.esc_html__('WooCommerce Order PDF plugin requires the WooCommerce plugin to be installed and activated!', 'wc-invoice').'</strong></p></div>';
            
        });
    }
}
add_action('plugins_loaded', 'wc_invoice_check_others_plugins_loaded');



function wc_invoice_shop_order_columns($columns) {
    $new_columns = array();    
    foreach( $columns as $column_name => $column_value ){
        $new_columns[$column_name] = $column_value;
        if ($column_name === "order_status") {            
            $new_columns['download_pdf'] = esc_html__('Download PDF', 'wc-invoice');
        }        
    }
    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'wc_invoice_shop_order_columns');



function wc_invoice_shop_order_column_content($column) {
    global $post;
    if ($column == 'download_pdf') {
        // $order = wc_get_order( $post->ID );
        $pdf_url = wp_nonce_url(admin_url('admin-ajax.php?action=get_order_details&order_id='.$post->ID), 'generate_wp_nonce');
        echo '<a class="download_pdf_col" href="'.$pdf_url.'">'.esc_html__('Download PDF', 'wc-invoice').'</a>';
    }
}
add_action('manage_shop_order_posts_custom_column', 'wc_invoice_shop_order_column_content');



// Add custom action button to order list actions
function wc_invoice_order_list_actions($actions, $order) {
    $order_id = $order->get_id();
    $action_slug = 'pdf_download_btn';
    $pdf_url = wp_nonce_url(admin_url('admin-ajax.php?action=get_order_details&order_id='.$order_id.'&my_account'), 'generate_wp_nonce');
   
    $actions[$action_slug] = array(
        'url'    => $pdf_url, // Replace with the actual URL you want the button to link to
        'name'   => __('Download PDF', 'wc-invoice')
    );    
    return $actions;
}
add_filter('woocommerce_my_account_my_orders_actions', 'wc_invoice_order_list_actions', 10, 2);



// Check the user and download order pdf 
function wc_invoice_get_order_details_pdf_ajax_code(){

    check_ajax_referer( 'generate_wp_nonce', 'security' );
    $order_id = filter_input( INPUT_GET, 'order_id', FILTER_VALIDATE_INT );

    if (isset($order_id) && !empty($order_id)) {

        $allowed = true;

        // Check if user is logged in.
        if (! is_user_logged_in()) {
            $allowed = false;
        }

        // Check the user privileges.
        if (!(current_user_can('manage_woocommerce_orders') || current_user_can('edit_shop_orders') ) && ! isset( $_GET['my-account'] ) ) {
            $allowed = false;
        }

        // Check current user can view order.
        if ( ! current_user_can('manage_options') && isset( $_GET['my-account'] ) ) {
            if ( ! current_user_can('view_order', $order_id )) {
                $allowed = false;
            }
        }

        if ( ! $allowed ) {
            wp_die( esc_html('You do not have sufficient permission to access this page', 'wc-invoice' ) );
        }

        $order = wc_get_order( $order_id );
        
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $billing_email = $order->get_billing_email();
        $order_date = $order->get_date_created();
        $currency = $order->get_currency();
        $order_total        = $order->get_total();
        $order_status       = $order->get_status();
        $order_items        = $order->get_items();
        $payment_method     = $order->get_payment_method_title();
        $billing_address    = $order->get_formatted_billing_address();
        $shipping_address   = $order->get_formatted_shipping_address();

        $site_logo_id = get_theme_mod('custom_logo');
        $site_logo = wp_get_attachment_image_src($site_logo_id, 'full');
        
        $store_address = get_option('woocommerce_store_address');
        $store_address_2 = get_option('woocommerce_store_address_2');
        $store_city = get_option('woocommerce_store_city');
        $store_pincode = get_option('woocommerce_store_postcode');

        // The Country and State
        $store_row_country = get_option('woocommerce_default_country');

        // Split the country/state.
        $conutry_split = explode(':', $store_row_country);

        $store_country = $conutry_split[0];
        $store_state = $conutry_split[1];

        $wc_store_address = ''; //<b>Store Address : </b></br></br>
        $wc_store_address .= $store_address .'<br>';
        $wc_store_address .= ($store_address_2) ? $store_address_2 . '<br>' : ''; 
        $wc_store_address .= $store_city . ', ' . $store_state . ' ' . $store_pincode .'<br>';
        $wc_store_address .= $store_country;

        $html = '';

        $html .= '<table style="width:100%; border:none; width:100%; max-width:500px; margin-left:auto; margin-right:auto; border:0; margin-bottom:10mm;">';
        $html .= '<thead>';
        $html .= '<th style="width:50%; text-align:left;">Store Logo</th>';
        $html .= '<th style="width:50%; text-align:right;">Store Address</th>';
        $html .= '</thead>';
        $html .= '<tbody>';
        if (empty($site_logo[0])){
            $html .= '<td style="width:50%; text-align:left;"><h2>'. get_bloginfo('name') .'</h2></td>';
        } {
            $html .= '<td style="width:50%; text-align:left;"><img style="max-width:100px" src="' . $site_logo[0] . '"></td>';
        }
        $html .= '<td style="width:50%; text-align:right;"><p>' . $wc_store_address . '</p></td>';
        $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<table  cellpadding="10" cellspacing="0" border="1" style="border:1px solid #6A6E71;width:100%; max-width:500px; margin-left:auto; margin-right:auto;">';

        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Order Number', 'wc-invoice' ) . '</th><td style="text-align:right;">#' . $order_id . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Order Date', 'wc-invoice' ) . '</th><td style="text-align:right;">' . date_format($order_date, 'Y/m/d H:i:s') . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'First Name', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $billing_first_name . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Last Name', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $billing_last_name . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Email Address', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $billing_email . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Billing Address', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $billing_address . '</th></tr>';
        if (!empty($shipping_address)) {
            $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Shipping Address', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $shipping_address . '</th></tr>';
        }
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Order Status', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $order_status . '</th></tr>';
        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Payment Method', 'wc-invoice' ) . '</th><td style="text-align:right;">' . $payment_method . '</th></tr>';
        
        ob_start();
        do_action( 'wc_invoice_order_invoice_dpf_add_extra_order_details', $order );
        $html .= ob_get_clean();

        $html .= '<tr><th style="text-align:left;">' . esc_html__( 'Items', 'wcorderpdf' ) . '</th>';

        $html .= '<td><table cellpadding="5" cellspacing="0" border="1" style="border:1px solid  #6A6E71;width:100%"><tr><td ><strong>' . esc_html__( 'Item Name', 'wcorderpdf' ) . '</strong></td><td ><strong>' . esc_html__( 'Quantity', 'wcorderpdf' ) . '</strong></td><td ><strong>' . esc_html__( 'Price', 'wcorderpdf' ) . '</strong></td></tr>';

        foreach ( $order_items as $item_id => $order_item ) {
            $html .= '<tr><td>' . $order_item->get_name() . '</td><td>' . number_format( $order_item->get_quantity(), 2, '.', '' ) . '</td><td>' . $currency . ' ' . number_format( $order_item->get_total(), 2, '.', '' ) . '</td></tr>';
        }

        $html .= '</table></td></tr>';
        $html .= '<tr><th>' . esc_html__( 'Order Total', 'wcorderpdf' ) . '</td><td style="text-align:right;">' . $currency . ' ' . number_format( $order_total, 2, '.', '' ) . '</td></tr>';

        $html .= '</table>';   
        $html .= '<p style="text-align:center; padding:15px 0; font-weight:bold;">Thank you ' .$billing_first_name. ' for shoping with us..!</p>';
       
        $filename = 'order-' . $order_id;

        $options = new Options();
        $options->set( 'isRemoteEnabled', true );
        $options->set( 'isHtml5ParserEnabled', true );
        $options->set( 'defaultFont', 'Courier' );

        $dompdf = new DOMPDF( $options );

        $dompdf->loadHtml( $html );
        $dompdf->setPaper( 'A4', 'portrait' );
        $dompdf->render();
        $dompdf->stream( $filename, array( 'Attachment' => 1 ) );
    }

    exit;

}

add_action( 'wp_ajax_get_order_details', 'wc_invoice_get_order_details_pdf_ajax_code' );
add_action( 'wp_ajax_nopriv_get_order_details', 'wc_invoice_get_order_details_pdf_ajax_code' );


