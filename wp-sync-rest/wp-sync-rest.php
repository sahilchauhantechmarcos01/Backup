<?php 
/*
Plugin Name: WP-Sync-Rest
Plugin URI: https://wordpress.com
Description: Sync Posts and Pages 
Version: 1.0.0
Requires PHP: 7.2
Author: DB01
License:  GPL v2 or later
Text Domain: wp-sync-rest
*/

defined('ABSPATH') or die('Not acccessible');


class sync{
    public $key = '';
    public $myallposts = array();
    function __construct(){
     $this->create_key();
     add_action('admin_enqueue_scripts', [$this,'sync_admin_styles']);
     add_action('admin_menu', [$this, 'plugin_admin_menu']);
     $this->create_endpoint();
    }
    function sync_admin_styles($hook) {
if (strpos($hook, 'wp-sync-rest') === false) {
    return;
}
    wp_enqueue_style(
        'sync-admin-style',
        plugin_dir_url(__FILE__) . 'style.css'
    );
      wp_enqueue_script(
        'sync-admin-script',
        plugin_dir_url(__FILE__) . 'main.js',
        [],              
        false,           
        true              
    );
    $key = get_option('wp_sync_rest_key');
    $url = home_url();
    $myallpostsItems = $this->getMyPosts();
   wp_localize_script('sync-admin-script', 'syncData', [
    'api_key' => $key,
    'home_url' => $url,
    'myallpostsItems' => $myallpostsItems,
]);
}
function create_key(){
    $this->key = get_option('wp_sync_rest_key');
if (!$this->key) {
     $this->key = bin2hex(random_bytes(16)); 
    update_option('wp_sync_rest_key',  $this->key);
}

}
function create_endpoint(){
   add_action('rest_api_init', function(){
   register_rest_route(
    'sync-api/v1',
    '/setall',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
             $body = json_decode($request->get_body());
             return $this->do_api_action($body);
        }
    ]
   );
   register_rest_route(
    'sync-api/v1',
    '/setpages',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
             $body = json_decode($request->get_body());
             return $this->do_api_action_pages($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/setposts',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
             $body = json_decode($request->get_body());
             return $this->do_api_action_posts($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/customSet',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
             $body = json_decode($request->get_body());
             return $this->do_api_action_custom($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/setwebsitedata',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
             $body = json_decode($request->get_body());
             return $this->websiteListData($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/getwebsitedata',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'GET',
        'callback' => function(WP_REST_Request $request){
             return $this->get_sites_list();
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/deletesite',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
            $body = json_decode($request->get_body());
             return $this->delete_site($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/syncData',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
            $body = json_decode($request->get_body());
             return $this->sync_data($body);
        }
    ]
   );
   
      register_rest_route(
    'sync-api/v1',
    '/syncWooData/products/get',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
              $body = json_decode($request->get_body());
             return $this->   get_all_products($body) ;//sync_orders_and_products($body);
        }
    ]
   );
      register_rest_route(
    'sync-api/v1',
    '/syncWooData/products/set',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
            $body = json_decode($request->get_body());
             return $this->   set_all_products($body) ;//sync_orders_and_products($body);
        }
    ]
   );
         register_rest_route(
    'sync-api/v1',
    '/syncWooData/orders/get',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
              $body = json_decode($request->get_body());
             return $this->   get_all_orders($body) ;//sync_orders_and_products($body);
        }
    ]
   );
         register_rest_route(
    'sync-api/v1',
    '/syncWooData/orders/set',
    [
        'permission_callback' => function(WP_REST_Request $request){
            return true;
        },
        'methods' => 'POST',
        'callback' => function(WP_REST_Request $request){
              $body = json_decode($request->get_body());
             return $this->   set_all_orders($body) ;//sync_orders_and_products($body);
        }
    ]
   );
   });   
}
function set_all_orders($body) {
    if (!isset($body->key) || $body->key !== $this->key) {
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }

    $count = 0;
    $errors = [];
    $processed_order_ids = []; // Track processed orders to avoid duplicates

    foreach ($body->all_orders as $order_data) {
        try {
            $order = null;
            $found_by = 'none';
            
            // DEBUG: Log what we're processing
            error_log("Processing order - unique_key: " . ($order_data->unique_key ?? 'none') . 
                     ", order_key: " . ($order_data->order_key ?? 'none') . 
                     ", id: " . ($order_data->id ?? 'none'));

            // METHOD 1: Search by unique_key
            if (!empty($order_data->unique_key)) {
                // Use direct database query for better accuracy
                global $wpdb;
                $sql = $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                     WHERE meta_key = '_unique_order_key' 
                     AND meta_value = %s 
                     LIMIT 1",
                    $order_data->unique_key
                );
                
                $existing_order_id = $wpdb->get_var($sql);
                
                if ($existing_order_id) {
                    // Check if we already processed this order
                    if (in_array($existing_order_id, $processed_order_ids)) {
                        error_log("Order ID $existing_order_id already processed, skipping duplicate");
                        continue; // Skip this duplicate
                    }
                    
                    $order = wc_get_order($existing_order_id);
                    if ($order) {
                        $found_by = 'unique_key';
                        error_log("Found existing order ID: $existing_order_id by unique_key");
                        $processed_order_ids[] = $existing_order_id;
                    }
                }
            }

            // METHOD 2: Search by WooCommerce order_key
            if (!$order && !empty($order_data->order_key)) {
                $order_id = wc_get_order_id_by_order_key($order_data->order_key);
                if ($order_id) {
                    // Check if already processed
                    if (in_array($order_id, $processed_order_ids)) {
                        error_log("Order ID $order_id already processed (by order_key), skipping");
                        continue;
                    }
                    
                    $order = wc_get_order($order_id);
                    if ($order) {
                        $found_by = 'order_key';
                        error_log("Found order by order_key: ID: $order_id");
                        $processed_order_ids[] = $order_id;
                    }
                }
            }

            // METHOD 3: Search by ID
            if (!$order && !empty($order_data->id)) {
                // Check if already processed
                if (in_array($order_data->id, $processed_order_ids)) {
                    error_log("Order ID {$order_data->id} already processed, skipping");
                    continue;
                }
                
                $order = wc_get_order($order_data->id);
                if ($order) {
                    $found_by = 'id';
                    error_log("Found order by ID: {$order_data->id}");
                    $processed_order_ids[] = $order_data->id;
                }
            }

            // Create new order if not found
            if (!$order) {
                error_log("Creating new order...");
                $order = wc_create_order();
                if ($order) {
                    $found_by = 'created_new';
                    $new_order_id = $order->get_id();
                    error_log("Created new order ID: $new_order_id");
                    $processed_order_ids[] = $new_order_id;
                } else {
                    error_log("Failed to create new order!");
                    $errors[] = "Failed to create order object";
                    continue;
                }
            }

            // Set order properties
            if (!empty($order_data->status)) {
                $order->set_status($order_data->status);
            }

            if (!empty($order_data->currency)) {
                $order->set_currency($order_data->currency);
            }

            if (!empty($order_data->customer_id)) {
                $order->set_customer_id($order_data->customer_id);
            }

 if (!empty($order_data->customer_ip_address)) {
                $order->set_customer_ip_address($order_data->customer_ip_address);
            }

            if (!empty($order_data->customer_user_agent)) {
                $order->set_customer_user_agent($order_data->customer_user_agent);
            }

            if (!empty($order_data->customer_note)) {
                $order->set_customer_note($order_data->customer_note);
            }
            $billing = [
                'first_name' => $order_data->billing_first_name ?? '',
                'last_name'  => $order_data->billing_last_name ?? '',
                'company'    => $order_data->billing_company ?? '',
                'address_1'  => $order_data->billing_address_1 ?? '',
                'address_2'  => $order_data->billing_address_2 ?? '',
                'city'       => $order_data->billing_city ?? '',
                'state'      => $order_data->billing_state ?? '',
                'postcode'   => $order_data->billing_postcode ?? '',
                'country'    => $order_data->billing_country ?? '',
                'email'      => $order_data->billing_email ?? '',
                'phone'      => $order_data->billing_phone ?? ''
            ];

            $order->set_address($billing, 'billing');
            $shipping = [
                'first_name' => $order_data->shipping_first_name ?? '',
                'last_name'  => $order_data->shipping_last_name ?? '',
                'company'    => $order_data->shipping_company ?? '',
                'address_1'  => $order_data->shipping_address_1 ?? '',
                'address_2'  => $order_data->shipping_address_2 ?? '',
                'city'       => $order_data->shipping_city ?? '',
                'state'      => $order_data->shipping_state ?? '',
                'postcode'   => $order_data->shipping_postcode ?? '',
                'country'    => $order_data->shipping_country ?? ''
            ];

            $order->set_address($shipping, 'shipping');

            if (!empty($order_data->payment_method)) {
                $order->set_payment_method($order_data->payment_method);
            }

            if (!empty($order_data->payment_method_title)) {
                $order->set_payment_method_title($order_data->payment_method_title);
            }

            if (!empty($order_data->transaction_id)) {
                $order->set_transaction_id($order_data->transaction_id);
            }

            foreach ($order->get_items() as $item_id => $item) {
                $order->remove_item($item_id);
            }

            if (!empty($order_data->items)) {
                foreach ($order_data->items as $item) {
                    $product_id = $item->product_id ?? 0;
                    $quantity   = $item->quantity ?? 1;

                    if ($product_id) {
                        $order->add_product(wc_get_product($product_id), $quantity);
                    }
                }
            }

            if (!empty($order_data->shipping_total)) {
                $order->set_shipping_total($order_data->shipping_total);
            }

            if (!empty($order_data->shipping_tax)) {
                $order->set_shipping_tax($order_data->shipping_tax);
            }

            if (!empty($order_data->discount_total)) {
                $order->set_discount_total($order_data->discount_total);
            }

            if (!empty($order_data->discount_tax)) {
                $order->set_discount_tax($order_data->discount_tax);
            }

            if (!empty($order_data->total_tax)) {
                $order->set_total_tax($order_data->total_tax);
            }

            if (!empty($order_data->total)) {
                $order->set_total($order_data->total);
            }

            if (!empty($order_data->date_created)) {
                $order->set_date_created($order_data->date_created);
            }

            if (!empty($order_data->date_modified)) {
                $order->set_date_modified($order_data->date_modified);
            }

            if (!empty($order_data->date_paid)) {
                $order->set_date_paid($order_data->date_paid);
            }

            if (!empty($order_data->date_completed)) {
                $order->set_date_completed($order_data->date_completed);
            }
            // Save the order
            $order_id = $order->save();
            
            // Update the unique_key meta (ONLY ONCE, at the end)
            if (!empty($order_data->unique_key)) {
                update_post_meta($order_id, '_unique_order_key', $order_data->unique_key);
                error_log("Updated unique_key for order ID: $order_id");
            }
            
            // Also ensure order_key is set if provided
            if (!empty($order_data->order_key) && empty($order->get_order_key())) {
                update_post_meta($order_id, '_order_key', $order_data->order_key);
            }

            if ($order_id) {
                $count++;
                error_log("Successfully saved order ID: $order_id (found by: $found_by)");
            } else {
                $errors[] = "Failed to save order: " . ($order_data->id ?? 'Unknown');
            }

        } catch (Exception $e) {
            $errors[] = "Error saving order: " . $e->getMessage();
            error_log("WC Order Sync Error: " . $e->getMessage());
        }
    }
    
    $response = [
        "Response" => "$count orders processed successfully",
        "processed_ids" => $processed_order_ids
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    return $response;
}
function set_all_orders_0($body) {

    if (!isset($body->key) || $body->key !== $this->key) {
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }

    $count  = 0;
    $errors = [];

    foreach ($body->all_orders as $order_data) {
        try {

            // if (!empty($order_data->id)) {
            //     $order = wc_get_order($order_data->id);
            //     if (!$order) {
            //         $order = wc_create_order();
            //     }
            // } else {
            //     $order = wc_create_order();
            // }
$order = null;

// First, try to find by unique_key if it exists
if (!empty($order_data->unique_key)) {
    // Use a direct WP_Query with meta_query for efficiency
    $args = [
        'post_type'      => 'shop_order',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => '_unique_order_key',
                'value' => $order_data->unique_key,
                'compare' => '='
            ]
        ]
    ];
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        $order_id = $query->posts[0];
        $order = wc_get_order($order_id);
        error_log("Found existing order ID: $order_id by unique_key: {$order_data->unique_key}");
        wp_reset_postdata();
    } else {
        error_log("No order found with unique_key: {$order_data->unique_key}");
        
        // Fallback: Also check by order_key if available
        if (!empty($order_data->order_key)) {
            $order_id = wc_get_order_id_by_order_key($order_data->order_key);
            if ($order_id) {
                $order = wc_get_order($order_id);
                error_log("Found order by order_key: {$order_data->order_key}, ID: $order_id");
            }
        }
    }
}

// If still not found, check by ID (if provided)
if (!$order && !empty($order_data->id)) {
    $order = wc_get_order($order_data->id);
    if ($order) {
        error_log("Found order by ID: {$order_data->id}");
    }
}

// Create new order if no existing found
if (!$order) {
    $order = wc_create_order();
    if ($order) {
        error_log("Created new order ID: " . $order->get_id());
    } else {
        error_log("Failed to create new order!");
        $errors[] = "Failed to create order object";
        continue; // Skip to next order
    }
}

// Always update the unique_key even if order was found by other means
if (!empty($order_data->unique_key)) {
    update_post_meta($order->get_id(), '_unique_order_key', $order_data->unique_key);
    error_log("Set/Updated unique_key for order ID: " . $order->get_id());
}

            if (!$order) {
                $errors[] = "Unable to create order object";
                continue;
            }

            if (!empty($order_data->status)) {
                $order->set_status($order_data->status);
            }

            if (!empty($order_data->currency)) {
                $order->set_currency($order_data->currency);
            }

            if (!empty($order_data->customer_id)) {
                $order->set_customer_id($order_data->customer_id);
            }

            if (!empty($order_data->customer_ip_address)) {
                $order->set_customer_ip_address($order_data->customer_ip_address);
            }

            if (!empty($order_data->customer_user_agent)) {
                $order->set_customer_user_agent($order_data->customer_user_agent);
            }

            if (!empty($order_data->customer_note)) {
                $order->set_customer_note($order_data->customer_note);
            }
            $billing = [
                'first_name' => $order_data->billing_first_name ?? '',
                'last_name'  => $order_data->billing_last_name ?? '',
                'company'    => $order_data->billing_company ?? '',
                'address_1'  => $order_data->billing_address_1 ?? '',
                'address_2'  => $order_data->billing_address_2 ?? '',
                'city'       => $order_data->billing_city ?? '',
                'state'      => $order_data->billing_state ?? '',
                'postcode'   => $order_data->billing_postcode ?? '',
                'country'    => $order_data->billing_country ?? '',
                'email'      => $order_data->billing_email ?? '',
                'phone'      => $order_data->billing_phone ?? ''
            ];

            $order->set_address($billing, 'billing');
            $shipping = [
                'first_name' => $order_data->shipping_first_name ?? '',
                'last_name'  => $order_data->shipping_last_name ?? '',
                'company'    => $order_data->shipping_company ?? '',
                'address_1'  => $order_data->shipping_address_1 ?? '',
                'address_2'  => $order_data->shipping_address_2 ?? '',
                'city'       => $order_data->shipping_city ?? '',
                'state'      => $order_data->shipping_state ?? '',
                'postcode'   => $order_data->shipping_postcode ?? '',
                'country'    => $order_data->shipping_country ?? ''
            ];

            $order->set_address($shipping, 'shipping');

            if (!empty($order_data->payment_method)) {
                $order->set_payment_method($order_data->payment_method);
            }

            if (!empty($order_data->payment_method_title)) {
                $order->set_payment_method_title($order_data->payment_method_title);
            }

            if (!empty($order_data->transaction_id)) {
                $order->set_transaction_id($order_data->transaction_id);
            }

            foreach ($order->get_items() as $item_id => $item) {
                $order->remove_item($item_id);
            }

            if (!empty($order_data->items)) {
                foreach ($order_data->items as $item) {
                    $product_id = $item->product_id ?? 0;
                    $quantity   = $item->quantity ?? 1;

                    if ($product_id) {
                        $order->add_product(wc_get_product($product_id), $quantity);
                    }
                }
            }

            if (!empty($order_data->shipping_total)) {
                $order->set_shipping_total($order_data->shipping_total);
            }

            if (!empty($order_data->shipping_tax)) {
                $order->set_shipping_tax($order_data->shipping_tax);
            }

            if (!empty($order_data->discount_total)) {
                $order->set_discount_total($order_data->discount_total);
            }

            if (!empty($order_data->discount_tax)) {
                $order->set_discount_tax($order_data->discount_tax);
            }

            if (!empty($order_data->total_tax)) {
                $order->set_total_tax($order_data->total_tax);
            }

            if (!empty($order_data->total)) {
                $order->set_total($order_data->total);
            }

            if (!empty($order_data->date_created)) {
                $order->set_date_created($order_data->date_created);
            }

            if (!empty($order_data->date_modified)) {
                $order->set_date_modified($order_data->date_modified);
            }

            if (!empty($order_data->date_paid)) {
                $order->set_date_paid($order_data->date_paid);
            }

            if (!empty($order_data->date_completed)) {
                $order->set_date_completed($order_data->date_completed);
            }
            $order_id = $order->save();
             $unique_key = $order_data->order_key; 
            update_post_meta($order_id, '_unique_order_key', $unique_key);

            if ($order_id) {
                $count++;
            } else {
                $errors[] = "Failed to save order: " . ($order_data->id ?? 'Unknown');
            }


        } catch (Exception $e) {

            $errors[] = "Error saving order: " . $e->getMessage();
            error_log("WC Order Sync Error: " . $e->getMessage());
        }
    }
    $response = ["Response" => "$count orders saved successfully"];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    return $response;
}

function get_all_orders($body) {
    if (!isset($body->key) || $body->key !== $this->key) {
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }

    $args = [
        'limit'  => -1,
        'status' => array_keys(wc_get_order_statuses())
    ];

    $orders = wc_get_orders($args);
    $all_orders = [];

    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $items_array = [];
        foreach ($order->get_items() as $item_id => $item) {
            $items_array[] = [
                'item_id'      => $item_id,
                'product_id'   => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'name'         => $item->get_name(),
                'quantity'     => $item->get_quantity(),
                'subtotal'     => $item->get_subtotal(),
                'total'        => $item->get_total(),
                'subtotal_tax' => $item->get_subtotal_tax(),
                'tax_class'    => $item->get_tax_class(),
                'tax_status'   => $item->get_tax_status(),
                'meta_data'    => $item->get_meta_data(),
            ];
        }
        $coupon_codes = $order->get_coupon_codes();
        $unique_key = $order->get_order_key(); 
        $tempOrder = [
            'id'                    => $order_id,
            'order_key'             => $order->get_order_key(),
            'status'                => $order->get_status(),
            'currency'              => $order->get_currency(),
            'discount_total'        => $order->get_discount_total(),
            'discount_tax'          => $order->get_discount_tax(),
            'shipping_total'        => $order->get_shipping_total(),
            'shipping_tax'          => $order->get_shipping_tax(),
            'cart_tax'              => $order->get_cart_tax(),
            'total_tax'             => $order->get_total_tax(),
            'total'                 => $order->get_total(),
            'total_refunded'        => $order->get_total_refunded(),
            'items'                 => $items_array,
            'coupon_codes'          => $coupon_codes,
            'date_created'          => $order->get_date_created(),
            'date_modified'         => $order->get_date_modified(),
            'date_completed'        => $order->get_date_completed(),
            'date_paid'             => $order->get_date_paid(),
            'customer_id'           => $order->get_customer_id(),
            'customer_ip_address'   => $order->get_customer_ip_address(),
            'customer_user_agent'   => $order->get_customer_user_agent(),
            'created_via'           => $order->get_created_via(),
            'customer_note'         => $order->get_customer_note(),
            'billing_first_name'    => $order->get_billing_first_name(),
            'billing_last_name'     => $order->get_billing_last_name(),
            'billing_company'       => $order->get_billing_company(),
            'billing_address_1'     => $order->get_billing_address_1(),
            'billing_address_2'     => $order->get_billing_address_2(),
            'billing_city'          => $order->get_billing_city(),
            'billing_state'         => $order->get_billing_state(),
            'billing_postcode'      => $order->get_billing_postcode(),
            'billing_country'       => $order->get_billing_country(),
            'billing_email'         => $order->get_billing_email(),
            'billing_phone'         => $order->get_billing_phone(),
            'shipping_first_name'   => $order->get_shipping_first_name(),
            'shipping_last_name'    => $order->get_shipping_last_name(),
            'shipping_company'      => $order->get_shipping_company(),
            'shipping_address_1'    => $order->get_shipping_address_1(),
            'shipping_address_2'    => $order->get_shipping_address_2(),
            'shipping_city'         => $order->get_shipping_city(),
            'shipping_state'        => $order->get_shipping_state(),
            'shipping_postcode'     => $order->get_shipping_postcode(),
            'shipping_country'      => $order->get_shipping_country(),
            'payment_method'        => $order->get_payment_method(),
            'payment_method_title'  => $order->get_payment_method_title(),
            'transaction_id'        => $order->get_transaction_id(),
            'view_order_url'        => $order->get_view_order_url(),
            'edit_order_url'        => $order->get_edit_order_url(),
            'unique_key'            => $unique_key,
        ];

        $all_orders[] = $tempOrder;
        update_post_meta($order->get_id(), '_unique_order_key', $unique_key);
    }

    return $all_orders;
}


function set_all_products($body) {
        if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    $count = 0;
    $errors = [];
foreach ($body->all_products as $product_data) {
    try {
if (!empty($product_data->id)) {

    $product = wc_get_product($product_data->id);

    if (!$product) {
        // Check if product with same name exists
        $existing_id = wc_get_product_id_by_sku($product_data->sku);

        if ($existing_id) {
            $product = wc_get_product($existing_id);
        } else {
            $product = new WC_Product_Simple();
        }
    }

} else {
    $product = new WC_Product_Simple();
}

            // $product = new WC_Product_Simple();
            $product->set_name($product_data->name ?? '');
            $product->set_slug($product_data->slug ?? '');
            $product->set_status($product_data->status ?? 'publish');
            $product->set_featured($product_data->featured ?? false);
            $product->set_catalog_visibility($product_data->visibility ?? 'visible');
            $product->set_description($product_data->description ?? '');
            $product->set_short_description($product_data->short_description ?? '');
            $product->set_sku($product_data->sku ?? '');
            $product->set_menu_order($product_data->menu_order ?? 0);
            $product->set_virtual($product_data->virtual ?? false);
            $product->set_price($product_data->price ?? '');
            $product->set_regular_price($product_data->regular_price ?? '');
            $product->set_sale_price($product_data->sale_price ?? '');
            
            if (!empty($product_data->sale_start)) {
                $product->set_date_on_sale_from($product_data->sale_start);
            }
            if (!empty($product_data->sale_end)) {
                $product->set_date_on_sale_to($product_data->sale_end);
            }
            $product->set_manage_stock($product_data->manage_stock ?? false);
            $product->set_stock_quantity($product_data->stock_quantity ?? 0);
            $product->set_stock_status($product_data->stock_status ?? 'instock');
            $product->set_backorders($product_data->backorders ?? 'no');
            $product->set_sold_individually($product_data->sold_individually ?? false);
            $product->set_purchase_note($product_data->purchase_note ?? '');
            $product->set_tax_status($product_data->tax_status ?? 'taxable');
            $product->set_tax_class($product_data->tax_class ?? '');
            $product->set_shipping_class_id($product_data->shipping_class_id ?? 0);
            $product->set_weight($product_data->weight ?? '');
            $product->set_length($product_data->length ?? '');
            $product->set_width($product_data->width ?? '');
            $product->set_height($product_data->height ?? '');
            $product->set_upsell_ids($product_data->upsell_ids ?? []);
            $product->set_cross_sell_ids($product_data->cross_sell_ids ?? []);
            $product->set_parent_id($product_data->parent_id ?? 0);
            $product->set_attributes($product_data->attributes ?? []);
            $product->set_default_attributes($product_data->default_attributes ?? []);
            $product->set_category_ids($product_data->category_ids ?? []);
            $product->set_tag_ids($product_data->tag_ids ?? []);
            $product->set_downloadable($product_data->downloadable ?? false);
            $product->set_download_limit($product_datadownload_limit ?? -1);
            $product->set_download_expiry($product_datadownload_expiry ?? -1);
            $product->set_downloads($product_data->downloads ?? []);
            $product->set_image_id($product_data->image_id ?? 0);
            $product->set_gallery_image_ids($product_data->gallery_image_ids ?? []);
            $product_id = $product->save();
            if ($product_id) {
                $count++;
            } else {
                $errors[] = "Failed to save product: " . ($product_data->name ?? 'Unknown');
            }
            
        } catch (Exception $e) {
            $errors[] = "Error saving product: " . $e->getMessage();
            error_log("WC Product Sync Error: " . $e->getMessage());
        }
    }
    
    $response = ["Response" => "$count products saved successfully"];
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    return $response;
}
function get_all_products($body){
        if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    $args = ['limit' => -1 , 'status'=> 'publish'];
    $products = wc_get_products($args);
    $tempProd = [];
    $all_products = [];
    if ( ! empty( $products ) ) {
    foreach ( $products as $product ) {
        $product_id = $product->get_id();

        $tempProd = [
            'id'                    => $product_id,
            'type'                  => $product->get_type(),
            'name'                  => $product->get_name(),
            'slug'                  => $product->get_slug(),
            'date_created'          => $product->get_date_created(),
            'date_modified'         => $product->get_date_modified(),
            'status'                => $product->get_status(),
            'featured'              => $product->get_featured(),
            'visibility'            => $product->get_catalog_visibility(),
            'description'           => $product->get_description(),
            'short_description'     => $product->get_short_description(),
            'sku'                   => $product->get_sku(),
            'menu_order'            => $product->get_menu_order(),
            'virtual'               => $product->get_virtual(),
            'permalink'             => get_permalink($product_id),
            'price'                 => $product->get_price(),
            'regular_price'         => $product->get_regular_price(),
            'sale_price'            => $product->get_sale_price(),
            'sale_start'            => $product->get_date_on_sale_from(),
            'sale_end'              => $product->get_date_on_sale_to(),
            'total_sales'           => $product->get_total_sales(),
            'tax_status'            => $product->get_tax_status(),
            'tax_class'             => $product->get_tax_class(),
            'manage_stock'          => $product->get_manage_stock(),
            'stock_quantity'        => $product->get_stock_quantity(),
            'stock_status'          => $product->get_stock_status(),
            'backorders'            => $product->get_backorders(),
            'sold_individually'     => $product->get_sold_individually(),
            'purchase_note'         => $product->get_purchase_note(),
            'shipping_class_id'     => $product->get_shipping_class_id(),
            'weight'                => $product->get_weight(),
            'length'                => $product->get_length(),
            'width'                 => $product->get_width(),
            'height'                => $product->get_height(),
            'dimensions'            => $product->get_dimensions(),
            'upsell_ids'            => $product->get_upsell_ids(),
            'cross_sell_ids'        => $product->get_cross_sell_ids(),
            'parent_id'             => $product->get_parent_id(),
            'children'              => $product->get_children(),
            'attributes'            => $product->get_attributes(),
            'default_attributes'    => $product->get_default_attributes(),
            'category_ids'          => $product->get_category_ids(),
            'tag_ids'               => $product->get_tag_ids(),
            'category_list'         => wc_get_product_category_list($product_id),
            'downloads'             => $product->get_downloads(),
            'download_expiry'       => $product->get_download_expiry(),
            'downloadable'          => $product->get_downloadable(),
            'download_limit'        => $product->get_download_limit(),
            'image_id'              => $product->get_image_id(),
            'image_html'            => $product->get_image(),
            'gallery_image_ids'     => $product->get_gallery_image_ids(),
            'reviews_allowed'       => $product->get_reviews_allowed(),
            'rating_counts'         => $product->get_rating_counts(),
            'average_rating'        => $product->get_average_rating(),
            'review_count'          => $product->get_review_count(),
        ];
        array_push($all_products , $tempProd);
    }
    return $all_products;
}
}

function sync_data($body){
    if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    $post_type = ($body->postType === 'any') ? 'any' : sanitize_text_field($body->postType);
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $body->size, 
            'fields' => 'all', 
        );
        if($body->category !== 'all'){
         $args['category_name'] = $body->category;
         }
        
        $query = new WP_Query($args);
        $all_posts = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

        // $resultArray = array_merge($senderPosts , $all_posts);
        // $uniquePosts =  array_unique($resultArray);
        //   wp_insert_post( $uniquePosts,$wp_error = false );
foreach ($senderPosts as $remote_post) {
     $messageRes = 'Starting';
    //  $messageRes .= "Post type : $post_type | " . $remote_post['type'] . "\n";
    //  $messageRes .= "Category : $body->category | " . implode(', ', $remote_post['categories']) . "\n"; 

    if($post_type !== 'any' && $remote_post['type'] != $post_type) continue;
    $catNotInclude = false;
    foreach($remote_post['categories'] as $key => $val){
        if($val != $body->category ) $catNotInclude = true;
    }
    if($catNotInclude) continue;
   
    $taxonomyz = $body->taxonomy_name ?? '';

if (!empty($taxonomyz) && $taxonomyz !== 'all') {
    $postTaxonomies = $remote_post['taxonomies'] ?? [];
    
    if (!isset($postTaxonomies[$taxonomyz]) || !is_array($postTaxonomies[$taxonomyz]) || count($postTaxonomies[$taxonomyz]) === 0) {
        continue; 
    }
}


    $messageRes .= 'End';
    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            // If value is array, store first — matches wp core REST output
            if (is_array($value)) $value = reset($value);
            update_post_meta($post_id, $key, $value);
        }
    }
        if (!empty($remote_post['taxonomies'])) {
        foreach ($remote_post['taxonomies'] as $taxonomy => $term_ids) {
            if (!taxonomy_exists($taxonomy)) continue; // prevent errors
            wp_set_object_terms($post_id, $term_ids, $taxonomy);
        }
    }

    
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts,
    'message'   => $messageRes,
];
    
    
    return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}
function delete_site($body) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weblist';

    // Sanitize inputs
    $site_key = $body->site_key;
    $site_url = $body->site_url;

    // Delete the row
    $deleted = $wpdb->delete(
        $table_name,
        [
            'site_key' => $site_key,
            'site_url' => $site_url
        ],
        ['%s', '%s']
    );
}

function get_sites_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weblist';
    $results = $wpdb->get_results("SELECT site_key, site_url FROM $table_name", ARRAY_A);

    $sites = [];
    if ($results) {
        foreach ($results as $row) {
            $sites[] = [$row['site_key'], $row['site_url']];
        }
    }

    return $sites;
}

function websiteListData($body){
    global  $wpdb;
    $table_name = $wpdb->prefix . 'weblist';
    $charset_collate = $wpdb->get_charset_collate();

    if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'")){
        $sql = "
        CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            site_key VARCHAR(255),
            site_url VARCHAR(255),
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    if (!empty($body->site_key) && !empty($body->site_url)) {

        $site_key = sanitize_text_field($body->site_key);
        $site_url  = esc_url_raw($body->site_url);

        $wpdb->insert(
            $table_name,
            [
                'site_key' => $site_key,
                'site_url'  => $site_url
            ],
            ['%s', '%s']
        );

        return [
            'success' => true,
            'message' => 'Site added successfully'
        ];
    }

    return [
        'success' => false,
        'message' => 'Missing site_name or site_url'
    ];
}


function do_api_action_custom($body){
    if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }

    if($body->custom_type === 'post'  ){     // && post_type_exists( $body->type )
    if($body->custom_type === 'post') {
        $args = array(
            'post_type' => $body->type,
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'all', 
        );
        
        $query = new WP_Query($args);
        $all_posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

        // $resultArray = array_merge($senderPosts , $all_posts);
        // $uniquePosts =  array_unique($resultArray);
        //   wp_insert_post( $uniquePosts,$wp_error = false );
foreach ($senderPosts as $remote_post) {

    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts
];
    }
}else{
      return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}
    if($body->custom_type === 'category' ) {    // && term_exists( $body->type, 'category')
    if($body->custom_type === 'category') {
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'all', 
            'category_name'  => $body->type,   
        );
        
        $query = new WP_Query($args);
        $all_posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

        // $resultArray = array_merge($senderPosts , $all_posts);
        // $uniquePosts =  array_unique($resultArray);
        //   wp_insert_post( $uniquePosts,$wp_error = false );
foreach ($senderPosts as $remote_post) {

    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts
];
    }
}else{
      return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}    
    return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}
function do_api_action_pages($body){
    if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    
    if($body->type === 'page') {
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'all', 
        );
        
        $query = new WP_Query($args);
        $all_posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

foreach ($senderPosts as $remote_post) {

    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts
];
    }
    
    return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}
function do_api_action_posts($body){
    if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    
    if($body->type === 'post') {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'all', 
        );
        
        $query = new WP_Query($args);
        $all_posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

        // $resultArray = array_merge($senderPosts , $all_posts);
        // $uniquePosts =  array_unique($resultArray);
        //   wp_insert_post( $uniquePosts,$wp_error = false );
foreach ($senderPosts as $remote_post) {

    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts
];
    }
    
    return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}
function do_api_action($body){
    if(!isset($body->key) || $body->key !== $this->key){
        return new WP_REST_Response(['error' => 'Invalid key'], 403);
    }
    
    if($body->type === 'all') {
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'all', 
        );
        
        $query = new WP_Query($args);
        $all_posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $post_data = array(
                    'id' => $post->ID,
                    'date' => $post->post_date,
                    'date_gmt' => $post->post_date_gmt,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'slug' => $post->post_name,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'link' => get_permalink($post->ID),
                    'title' => array(
                        'rendered' => get_the_title($post->ID)
                    ),
                    'content' => array(
                        'rendered' => get_the_content(null, false, $post->ID),
                        'protected' => false
                    ),
                    'excerpt' => array(
                        'rendered' => get_the_excerpt($post->ID),
                        'protected' => false
                    ),
                    'author' => (int) $post->post_author,
                    'featured_media' => (int) get_post_thumbnail_id($post->ID),
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'sticky' => is_sticky($post->ID),
                    'template' => get_page_template_slug($post->ID),
                    'format' => get_post_format($post->ID) ?: 'standard',
                    'meta' => array(),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'ids')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'ids')),
                );
                
                $all_posts[] = $post_data;
            }
            wp_reset_postdata();
        } 
        $senderPosts = $body->myallpostsItems;
        $senderPosts = json_decode(json_encode($senderPosts), true);

        // $resultArray = array_merge($senderPosts , $all_posts);
        // $uniquePosts =  array_unique($resultArray);
        //   wp_insert_post( $uniquePosts,$wp_error = false );
foreach ($senderPosts as $remote_post) {

    $existing = get_page_by_path($remote_post['slug'], OBJECT, $remote_post['type']);

    $postarr = [
        'post_title'       => $remote_post['title']['rendered'],
        'post_name'        => $remote_post['slug'],
        'post_status'      => $remote_post['status'],
        'post_type'        => $remote_post['type'],
        'post_content'     => $remote_post['content']['rendered'],
        'post_excerpt'     => $remote_post['excerpt']['rendered'],
        'post_author'      => $remote_post['author'],
        'comment_status'   => $remote_post['comment_status'],
        'ping_status'      => $remote_post['ping_status'],
        'post_date'        => $remote_post['date'],
        'post_date_gmt'    => $remote_post['date_gmt'],
        'post_modified'    => $remote_post['modified'],
        'post_modified_gmt'=> $remote_post['modified_gmt'],
    ];

    if ($existing) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post($postarr);
    } else {
        $post_id = wp_insert_post($postarr);
    }

    if (is_wp_error($post_id)) continue;

    if (!empty($remote_post['categories'])) {
        wp_set_post_categories($post_id, $remote_post['categories']);
    }

    if (!empty($remote_post['tags'])) {
        wp_set_post_tags($post_id, $remote_post['tags']);
    }


    if (!empty($remote_post['featured_media'])) {
        set_post_thumbnail($post_id, $remote_post['featured_media']);
    }

 
    if (!empty($remote_post['format'])) {
        set_post_format($post_id, $remote_post['format']);
    }

  
    if (!empty($remote_post['template'])) {
        update_post_meta($post_id, '_wp_page_template', $remote_post['template']);
    }


    if (!empty($remote_post['sticky'])) {
        stick_post($post_id);
    } else {
        unstick_post($post_id);
    }


    if (!empty($remote_post['meta'])) {
        foreach ($remote_post['meta'] as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
}


        return [
    'received' => count($senderPosts),
    'posts'    => $all_posts
];
    }
    
    return new WP_REST_Response(['error' => 'Invalid type parameter'], 400);
}

function getMyPosts(){
    $args = array(
        'post_type' => 'any',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'all',
    );
    
    $query = new WP_Query($args);
    $all_posts = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $post;

           $post_data = [
    'id' => $post->ID,
    'date' => $post->post_date,
    'date_gmt' => $post->post_date_gmt,
    'modified' => $post->post_modified,
    'modified_gmt' => $post->post_modified_gmt,
    'slug' => $post->post_name,
    'status' => $post->post_status,
    'type' => $post->post_type,
    'link' => get_permalink($post->ID),
    'title' => ['rendered' => get_the_title($post->ID)],
    'content' => ['rendered' => get_the_content(), 'protected' => false],
    'excerpt' => ['rendered' => get_the_excerpt(), 'protected' => false],
    'author' => (int) $post->post_author,
    'featured_media' => (int) get_post_thumbnail_id($post->ID),
    'comment_status' => $post->comment_status,
    'ping_status' => $post->ping_status,
    'sticky' => is_sticky($post->ID),
    'template' => get_page_template_slug($post->ID),
    'format' => get_post_format($post->ID) ?: 'standard',
    'meta' => array_filter(
        get_post_meta($post->ID),
        fn($key) => !str_starts_with($key, '_'),
        ARRAY_FILTER_USE_KEY
    ),
    'categories' => wp_get_post_categories($post->ID, ['fields' => 'ids']),
    'tags' => wp_get_post_tags($post->ID, ['fields' => 'ids']),

    'taxonomies' => [],
];
    $taxonomies = get_object_taxonomies($post->post_type, 'objects');

    foreach ($taxonomies as $taxonomy => $tax_obj) {
        if (in_array($taxonomy, ['category', 'post_tag'])) continue; // already handled
        
        $terms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);
        $post_data['taxonomies'][$taxonomy] = $terms ?: [];
    }

            $all_posts[] = $post_data;
        }
        wp_reset_postdata();
    }

    return $all_posts;
}

function do_api_action_set_data($body){
        if($body->key !== $this->key){
         return 404;
    }
        elseif($body->type === 'post') {

        $postarr = [
            'post_title'   => sanitize_text_field($body->data->title),
            'post_content' => wp_kses_post($body->data->content),
            'post_status'  => 'publish',
            'post_type'    => 'post'
        ];

        $post_id = wp_insert_post($postarr);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['error' => 'Failed to create post'], 500);
        }

        return ['success' => true, 'post_id' => $post_id];
    }
}

function plugin_admin_menu(){
   add_menu_page(
    'WP Sync Rest Page',
    'WP Sync Rest',
    'manage_options',
    'wp-sync-rest',
    [$this, 'plugin_content'],
    'dashicons-controls-repeat',
    10
   );
   add_submenu_page(
    'wp-sync-rest',
    'Sync',
    'Sync',
    'manage_options',
    'plugin_sync',
    [$this,'sync_page'],
   );
}
function sync_page(){
       ?>
    <nav >
 <h1>WP Sync Data</h1>
 <p></p> 
    </nav>
    <div id="syncing"  class="dx-none">Syncing in Process...</div>
    <div class="settings">
        <p class="settingsHeading">Settings</p>
      
            <ul class="settingsMenu">
                <li>
                    <select name="selectPostType" id="selectPostType">
                        <option default value="all">All Posts</option>
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                
                    foreach ($post_types as $post_type) {
                        echo '<option value="' . esc_attr($post_type->name) . '">'
                            . $post_type->labels->singular_name
                            . '</option>';
                    }
                        ?>
                    </select>
                </li>
                <li>
                    <select name="selectCategory" id="selectCategory">
                        <option value="all">All Categories</option>

                        <?php
                        $categories = get_terms([
                            'taxonomy'   => 'category',
                            'hide_empty' => false,
                        ]);
                    
                        foreach ($categories as $cat) {
                            echo '<option value="' . $cat->term_id . '">'
                                . $cat->name .
                            '</option>';
                        }
                        ?>
                    </select>
                </li>
                <li>
                     <select name="selectTaxonomy" id="selectTaxonomy">
                         <option value="all">All Taxonomies</option>
                         <?php
                         $taxonomies = get_taxonomies(['public' => true], 'objects');
                         foreach ($taxonomies as $tax) {
                             if (in_array($tax->name, ['category', 'post_tag'])) continue;
                             echo '<option value="' . esc_attr($tax->name) . '">' . $tax->labels->singular_name . '</option>';
                         }
                         ?>
                      </select>
                </li>

                <li><input type="number" name="paginationSize" id="size" placeholder="Pagination Size"></li>
            </ul>
       <button type="button" id="applySettings">APPLY</button>
    </div>
    <main class="settingsMain">
       <div class="selectedSites"><div id="heading" >
        <p>Available Sites</p> 
        <div id="emptyHeading" class="hide">Empty</div>
        <button id="clear" type="button" style="display: none;" >Clear Sites</button></div>
        <ul id="urls"></ul>
        <button type="button" id="sync_Page_Enter" class="hide">SYNC</button>
       </div>
       <div id="wooData" class="hide">
        <p id="wooDataHeading">Sync Woocommerce Data</p>
        <p id="response" class="hide"></p>
        <button id="wooSync" type="button">SYNC</button>
       </div>
    </main>
     </div>
    </div>
    <?php
}

function plugin_content(){
    ?>
    <nav >
 <h1>WP Sync Data</h1>
 <p></p> 
    </nav>
    <div class="main">
     <section>
        <div id="syncing"  class="dx-none">Syncing in Process...</div>
        <form action="" id="sync_form">
            <div class="inputs">
            <label for="api_key">API Key</label>
            <input type="text" name="api_key" id="api_key" required placeholder="Enter Api Key..">
            <label for="Url" class="urlLabel">URLs</label>
            <input type="url" name="url" id="url" required placeholder="Enter urls">
            </div>
            <div class="empty_err">
            <p id="empty_url" class="hide">Please enter urls</p>
            <p id="empty_key" class="hide">Please enter key</p>
            </div>
            <button type="button" id="enterSite">ENTER SITE</button>
            <div id="emptySitesErr" class="hide">Please enter a site</div>
            <div class="selectedSites"><div id="heading" class="hide"><p>Selected Sites</p> <button id="clear" type="button" style="display: none;">Clear Sites</button></div><ul id="urls"></ul></div>
            <div class="buttons" style="display: none;"> 
            <button type="button" id="sync_posts">Sync Posts</button>
             <button type="button"  id="sync_pages">Sync Pages</button>
             <button  type="button" id="sync_all">Sync All</button>
             </div>
        </form>
        <div id="your_key">Show my key</div>
        <div class="customQuery" style="display: none;">
            <p>Customized Sync</p>
            <div class="query">
                <input type="text" name="post_type" id="postTypeInp" placeholder="Enter the Post Type">
                <button type="button" id ="customPostSync">Sync</button>
            </div>
            <div class="query">
                <input type="text" name="category" id="categoryInp" placeholder="Enter the Category">
                <button type="button" id="catSync">Sync</button>
            </div>
        </div>
     </section>
     <div>
     </div>
    </div>
    <?php
}
}


new sync();


