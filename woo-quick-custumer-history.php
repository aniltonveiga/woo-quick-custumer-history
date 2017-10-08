<?php
/*
*Plugin Name: WooCommerce Custumer History
*Plugin URI: http://www.adveiga.com.br
*Author: Anilton Veiga
*Author URI: http://www.adveiga.com.br
*Version: 1.0
*License: GPLv2
*/

//Exit if acessed directly
if (!defined('ABSPATH')){
  exit;
}

add_action('init',"load_text_domain");

function load_text_domain(){
  load_plugin_textdomain("woo-custumer-history",false,dirname(plugin_basename(__FILE__)));
}

add_action( 'add_meta_boxes', 'custumer_history_metabox' );
if ( ! function_exists( 'custumer_history_metabox' ) )
{
    function custumer_history_metabox()
    {
        add_meta_box( 'fields', __('Client History','woocommerce'), 'mv_add_other_fields_for_packaging', 'shop_order', 'side', 'high' );
    }
}

if ( ! function_exists( 'mv_save_wc_order_other_fields' ) ){

  function userInformations($userID){
  $users = get_users();
    foreach( $users as $user ) {
      $udata = get_userdata($userID);
      $registered = $udata->user_registered;
      return $registered;
    }
  }

  function getAllOrderCompleted($userID){
    $customer_orders = get_posts( array(
    'numberposts' => -1,
    'meta_key'    => '_customer_user',
    'meta_value'  => $userID,
    'post_type'   => wc_get_order_types(),
    'post_status' => 'wc-completed',
    ));

    return $customer_orders;
  }

function get_customer_total_order($userID) {
  $total = 0;
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $userID,
        'post_type'   => wc_get_order_types(),
        'post_status' => array( 'wc-completed' )
    ) );


    foreach ( $customer_orders as $customer_order ) {
        $order = wc_get_order( $customer_order );
        $total = $total+$order->get_total();
    }

    return $total;
}

    function mv_add_other_fields_for_packaging()
    {
        global $woocommerce, $post;
        $order = new WC_Order($post->ID);
        $custumerId = $order->customer_id;
        $custumerName = $order->billing_first_name;
        $userDataRegister = userInformations($order->customer_id);
        $allCompletedOrders = getAllOrderCompleted($order->customer_id);
        $totalOrders = get_customer_total_order($order->customer_id);
        ?>
        <style type="text/css">
          .hist-line{
            padding: 11px 0px;
            border-bottom: 1px solid #f1f1f1;
          }
          .hist-line:last-of-type{
            border-bottom: 0px;
          }

          .hist-badge-box{
            outline: 1px solid red;
            width: 50px;
            height: 50px;
            background-color:#ccc;
          }
        </style>
        <div class="hist-line">
          <strong>Custumer:</strong> <?php echo ($custumerName); ?><br />
        </div>
        <div class="hist-line">
          <strong>Registered in:</strong> <?php echo ($userDataRegister); ?><br />
        </div>
        <div class="hist-line">
          <strong>Purchases:</strong> <?php
          if(count($allCompletedOrders) > 0){
          echo (count($allCompletedOrders));
          }else{
          echo ("This customer has no completed purchase.");
          }
          ?> <br />
        </div>
        <div class="hist-line">
          <strong>Total on purchases:</strong> <?php
          if($totalOrders > 0){
            echo get_woocommerce_currency_symbol()."&nbsp;".$totalOrders;
          }else{
            echo("This customer has no completed purchase.");
          }

           ?> <br />
        </div>
        <?php
    }

}
