<?php

/**
 * Plugin core start
 * Checked Woocommerce activation
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    /**
     * moonlight gateway register
     */
    add_filter( 'woocommerce_payment_gateways', 'moonlight_payment_gateways' );
    function moonlight_payment_gateways( $gateways ) {
        $gateways[] = 'Moonlight_Gateway';
        return $gateways;
    }

    /**
     * moonlight gateway init
     */
    add_action( 'plugins_loaded', 'moonlight_gateway_plugin_activation' );
    function moonlight_gateway_plugin_activation() {

        class Moonlight_Gateway extends WC_Payment_Gateway {

            public function __construct() {

                // Plugin base information
                $this->id                 = 'moonlight_gateway'; // Unique ID for the payment gateway
                $this->icon               = PLUGIN_PUBLIC_ASSETS_URL . '/images/online-payment.png'; // Payment gateway icon
                $this->method_title       = esc_html__( "Moonlight", "mpg" ); // Gateway title in admin panel
                $this->method_description = esc_html__( "Moonlight Payment Gateway Options", "mpg" ); // Description in admin panel
                $this->has_fields         = true; // Set to true to display custom fields during checkout

                // Load the settings
                $this->init_settings();

                // Retrieve gateway settings
                $this->title       = $this->get_option( 'title', 'Moonlight Payment' ); // Payment title displayed to users
                $this->description = $this->get_option( 'description', 'Moonlight Payment Gateway' ); // Description for users
                $this->enabled     = $this->get_option( 'enabled' ); // Check if the gateway is enabled
                $this->testmode    = 'yes' === $this->get_option( 'testmode' ); // Check if test mode is enabled

                // Define form fields for gateway settings
                $this->moonlight_gateway_options_fields();

                // Hook to save admin settings
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            /**
             * Define Moonlight Gateway Options Fields
             * Displayed in WooCommerce > Settings > Payment Methods
             */
            public function moonlight_gateway_options_fields() {

                $this->form_fields = array(
                    'testmode'          => array(
                        'title'       => 'Test mode',
                        'label'       => 'Enable Test Mode',
                        'type'        => 'checkbox',
                        'description' => 'Place the payment gateway in test mode using test API keys.',
                        'default'     => 'yes',
                        'desc_tip'    => true,
                    ),
                    'enabled'           => array(
                        'title'       => 'Enable/Disable',
                        'label'       => 'Enable Moonlight Gateway',
                        'type'        => 'checkbox',
                        'description' => 'Set to "yes" to enable this payment gateway.',
                        'default'     => 'yes',
                        'desc_tip'    => true,
                    ),
                    'title'             => array(
                        'title'       => 'Title',
                        'type'        => 'text',
                        'description' => 'This controls the title which the user sees during checkout.',
                        'desc_tip'    => true,
                    ),
                    'description'       => array(
                        'title'       => 'Description',
                        'type'        => 'textarea',
                        'description' => 'This controls the description which the user sees during checkout.',
                        'desc_tip'    => true,
                    ),
                    'mpg_user_name'     => array(
                        'title'       => 'User Name',
                        'type'        => 'text',
                        'description' => 'API Login User provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'mpg_password'      => array(
                        'title'       => 'Password',
                        'type'        => 'password',
                        'description' => 'API Login Password provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'test_security_key' => array(
                        'title'       => 'Test Security Key',
                        'type'        => 'password',
                        'description' => 'API Test Security Key provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'live_security_key' => array(
                        'title'       => 'Live Security Key',
                        'type'        => 'password',
                        'description' => 'API Live Security Key provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                );
            }

            /**
             * Display payment fields on the checkout page.
             * This method renders input fields for the user to enter payment details.
             */
            public function payment_fields() {
                // Check if the payment gateway is in test mode
                if ( $this->testmode ) {
                    // Display message for test environment along with sample input fields
                    ?>
                    <p class="mpg-mb-0">
                        <?php esc_html_e( 'You are currently in Test Mode. No real transactions will be processed.', 'mpg' ); ?>
                    </p>
                    <p class="mpg-mb-0"><?php esc_html_e( 'Use the following test credentials to proceed:', 'mpg' ); ?></p>
                    <ul class="mpg-test-card-credentials">
                        <li><?php esc_html_e( 'Card Number: 4111 1111 1111 1111', 'mpg' ); ?></li>
                        <li><?php esc_html_e( 'Expiry Date: 10/25 (or any future date)', 'mpg' ); ?></li>
                    </ul>

                    <table>
                        <tr>
                            <td><label for="mpg_card_number"><?php esc_html_e( 'Card Number:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_card_number" id="mpg_card_number"
                                    placeholder="4111 1111 1111 1111"></td>
                        </tr>
                        <tr>
                            <td><label for="mpg_expiry_date"><?php esc_html_e( 'Expiry Date:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_expiry_date" id="mpg_expiry_date"
                                    placeholder="MM/YY"></td>
                        </tr>
                        <tr>
                            <td><label for="mpg_card_cvv"><?php esc_html_e( 'CVV:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_card_cvv" id="mpg_card_cvv" placeholder="CVV">
                            </td>
                        </tr>
                    </table>
                    <?php
                } else {
                    // Display live environment input fields with a user-friendly message
                    ?>
                    <p><?php esc_html_e( 'Please enter your payment details below to complete the transaction securely.', 'mpg' ); ?></p>

                    <table>
                        <tr>
                            <td><label for="mpg_card_number"><?php esc_html_e( 'Card Number:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_card_number" id="mpg_card_number"
                                    placeholder="Enter your card number"></td>
                        </tr>
                        <tr>
                            <td><label for="mpg_expiry_date"><?php esc_html_e( 'Expiry Date:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_expiry_date" id="mpg_expiry_date"
                                    placeholder="MM/YY"></td>
                        </tr>
                        <tr>
                            <td><label for="mpg_card_cvv"><?php esc_html_e( 'CVV:', 'mpg' ); ?></label></td>
                            <td><input class="mpg-card-input-field" type="text" name="mpg_card_cvv" id="mpg_card_cvv" placeholder="CVV">
                            </td>
                        </tr>
                    </table>
                    <?php
                }
            }

            /**
             * Process the payment and return the result.
             */
            public function process_payment( $order_id ) {

                // Get order data
                $order = wc_get_order( $order_id );

                // Determine the security key based on test mode
                $security_key = $this->testmode
                    ? $this->get_option( 'test_security_key' )
                    : $this->get_option( 'live_security_key' );

                // Use the security key
                if ( empty( $security_key ) ) {
                    throw new Exception( 'Security key is missing. Please check the payment gateway settings.' );
                }

                // Get billing and shipping information from the order
                $billing_first_name = $order->get_billing_first_name();
                $billing_last_name  = $order->get_billing_last_name();
                $billing_email      = $order->get_billing_email();
                $billing_address1   = $order->get_billing_address_1();
                $billing_address2   = $order->get_billing_address_2();
                $billing_city       = $order->get_billing_city();
                $billing_state      = $order->get_billing_state();
                $billing_postcode   = $order->get_billing_postcode();
                $billing_country    = $order->get_billing_country();
                $billing_phone      = $order->get_billing_phone();

                // get shipping address
                $shipping_first_name = $order->get_shipping_first_name();
                $shipping_last_name  = $order->get_shipping_last_name();
                $shipping_address1   = $order->get_shipping_address_1();
                $shipping_address2   = $order->get_shipping_address_2();
                $shipping_city       = $order->get_shipping_city();
                $shipping_state      = $order->get_shipping_state();
                $shipping_postcode   = $order->get_shipping_postcode();
                $shipping_country    = $order->get_shipping_country();

                // If shipping address is empty, use billing address
                if ( empty( $shipping_first_name ) && empty( $shipping_last_name ) && empty( $shipping_address1 ) ) {
                    $shipping_first_name = $billing_first_name;
                    $shipping_last_name  = $billing_last_name;
                    $shipping_address1   = $billing_address1;
                    $shipping_address2   = $billing_address2;
                    $shipping_city       = $billing_city;
                    $shipping_state      = $billing_state;
                    $shipping_postcode   = $billing_postcode;
                    $shipping_country    = $billing_country;
                }

                // Get order details
                $amount            = $order->get_total();
                $order_id          = $order->get_id();
                $order_description = sprintf( 'Order #%s - %s', $order_id, get_bloginfo( 'name' ) );

                // Get sensitive card data from POST
                $card_number = sanitize_text_field( $_POST['mpg_card_number'] ?? '' );
                $expiry_date = sanitize_text_field( $_POST['mpg_expiry_date'] ?? '' );
                $cvv         = sanitize_text_field( $_POST['mpg_card_cvv'] ?? '' );

                // Ensure required card data is provided
                if ( empty( $card_number ) || empty( $expiry_date ) ) {
                    wc_add_notice( __( 'Card details are required for payment.', 'woocommerce' ), 'error' );
                    return;
                }

                // Prepare data for API request
                $this->login = [
                    'security_key' => $security_key,
                ];

                $this->order = [
                    'ipaddress'        => WC_Geolocation::get_ip_address(),
                    'orderid'          => $order_id,
                    'orderdescription' => $order_description,
                    'tax'              => $order->get_total_tax(),
                    'shipping'         => $order->get_shipping_total(),
                    'ponumber'         => $order->get_id(),
                ];

                $this->billing = [
                    'firstname' => $billing_first_name,
                    'lastname'  => $billing_last_name,
                    'address1'  => $billing_address1,
                    'address2'  => $billing_address2,
                    'city'      => $billing_city,
                    'state'     => $billing_state,
                    'zip'       => $billing_postcode,
                    'country'   => $billing_country,
                    'phone'     => $billing_phone,
                    'email'     => $billing_email,
                    'website'   => get_bloginfo( 'url' ),
                ];

                $this->shipping = [
                    'firstname' => $shipping_first_name,
                    'lastname'  => $shipping_last_name,
                    'address1'  => $shipping_address1,
                    'address2'  => $shipping_address2,
                    'city'      => $shipping_city,
                    'state'     => $shipping_state,
                    'zip'       => $shipping_postcode,
                    'country'   => $shipping_country,
                    'email'     => $billing_email,
                ];

                // Call the API for payment processing
                $response = $this->do_sale( $amount, $card_number, $expiry_date, $cvv );
                put_program_logs( "API Response: " . json_encode( $response ) );
                update_option( 'mpg_api_response', json_encode( $response ) );

                if ( is_wp_error( $response ) ) {
                    wc_add_notice( __( 'Payment processing failed. Please try again.', 'woocommerce' ), 'error' );
                    return;
                }

                if ( isset( $response['success'] ) && $response['success'] ) {

                    // Mark order as processing or complete
                    $order->payment_complete();

                    // get response data
                    $response_data = $response['data'];

                    // get response text
                    $response_text = $response_data['responsetext'];
                    // get auth code
                    $auth_code = $response_data['authcode'];
                    // get transaction id
                    $transaction_id = $response_data['transactionid'];
                    // get order id
                    $_order_id = $response_data['orderid'];
                    // get order type
                    $order_type = $response_data['type'];
                    // get response code
                    $response_code = $response_data['response_code'];

                    // update post meta
                    update_post_meta( $order_id, '_mpg_response_text', $response_text );
                    update_post_meta( $order_id, '_mpg_auth_code', $auth_code );
                    update_post_meta( $order_id, '_mpg_transaction_id', $transaction_id );
                    update_post_meta( $order_id, '_mpg_order_id', $_order_id );
                    update_post_meta( $order_id, '_mpg_order_type', $order_type );
                    update_post_meta( $order_id, '_mpg_response_code', $response_code );

                    // Return success result
                    return [
                        'result'   => 'success',
                        'redirect' => $this->get_return_url( $order ),
                    ];
                } else {

                    $field_message = isset( $response['message'] ) ? $response['message'] : '';
                    // put_program_logs( 'Field Message: ' . $field_message );
                    update_option( 'mpg_field_message', $field_message );

                    // Add error notice
                    wc_add_notice( __( 'Payment failed: ' . $field_message, 'mpg' ), 'error' );
                    return;
                }
            }

            /**
             * Make the API request to process payment on the Moonlight Gateway.
             * @param mixed $amount
             * @param mixed $ccnumber
             * @param mixed $ccexp
             * @param mixed $cvv
             * @return array
             */
            function do_sale( $amount, $ccnumber, $ccexp, $cvv = "" ) {

                // Build the query parameters as an associative array
                $query = [
                    'security_key'       => $this->login['security_key'],
                    'ccnumber'           => $ccnumber,
                    'ccexp'              => $ccexp,
                    'amount'             => number_format( $amount, 2, ".", "" ),
                    'cvv'                => $cvv,
                    'ipaddress'          => $this->order['ipaddress'],
                    'orderid'            => $this->order['orderid'],
                    'orderdescription'   => $this->order['orderdescription'],
                    'tax'                => number_format( $this->order['tax'], 2, ".", "" ),
                    'shipping'           => number_format( $this->order['shipping'], 2, ".", "" ),
                    'ponumber'           => $this->order['ponumber'],
                    'firstname'          => $this->billing['firstname'],
                    'lastname'           => $this->billing['lastname'],
                    'company'            => $this->billing['company'],
                    'address1'           => $this->billing['address1'],
                    'address2'           => $this->billing['address2'],
                    'city'               => $this->billing['city'],
                    'state'              => $this->billing['state'],
                    'zip'                => $this->billing['zip'],
                    'country'            => $this->billing['country'],
                    'phone'              => $this->billing['phone'],
                    'fax'                => $this->billing['fax'],
                    'email'              => $this->billing['email'],
                    'website'            => $this->billing['website'],
                    'shipping_firstname' => $this->shipping['firstname'],
                    'shipping_lastname'  => $this->shipping['lastname'],
                    'shipping_company'   => $this->shipping['company'],
                    'shipping_address1'  => $this->shipping['address1'],
                    'shipping_address2'  => $this->shipping['address2'],
                    'shipping_city'      => $this->shipping['city'],
                    'shipping_state'     => $this->shipping['state'],
                    'shipping_zip'       => $this->shipping['zip'],
                    'shipping_country'   => $this->shipping['country'],
                    'shipping_email'     => $this->shipping['email'],
                    'type'               => 'sale',
                ];

                // put_program_logs( "do sale payload: " . json_encode( $query ) );
                update_option( 'mpg_api_request_payload', json_encode( $query ) );

                $do_sale_api_endpoint = "https://moonlight.transactiongateway.com/api/transact.php";
                $response             = wp_remote_post( $do_sale_api_endpoint, [
                    'timeout' => 60,
                    'body'    => $query,
                ] );

                // Handle response errors
                if ( is_wp_error( $response ) ) {
                    return [
                        'success' => false,
                        'message' => $response->get_error_message(),
                    ];
                }

                // Parse the response body
                $body = wp_remote_retrieve_body( $response );

                if ( empty( $body ) ) {
                    return [
                        'success' => false,
                        'message' => 'Empty response from the server.',
                    ];
                }

                // Parse the response data
                $data = [];
                parse_str( $body, $data );

                // Check if the response is successful
                if ( isset( $data['response'] ) && $data['response'] == 1 ) {
                    return [
                        'success' => true,
                        'data'    => $data,
                    ];
                }

                // Handle unsuccessful responses
                return [
                    'success' => false,
                    'message' => isset( $data['responsetext'] ) ? $data['responsetext'] : 'Transaction failed.',
                    'data'    => $data,
                ];
            }
        }

    }

} else {
    /**
     * Admin Notice
     */
    add_action( 'admin_notices', 'moonlight_gateway_admin_notice__error' );
    function moonlight_gateway_admin_notice__error() {
        ?>
        <div class="notice notice-error">
            <p><a href="http://wordpress.org/extend/plugins/woocommerce/"><?php esc_html_e( 'Woocommerce', 'mpg' ); ?></a>
                <?php esc_html_e( 'plugin needs to actived if you want to install this plugin.', 'mpg' ); ?></p>
        </div>
        <?php
    }

    /**
     * Deactivate Plugin
     */
    add_action( 'admin_init', 'moonlight_gateway_deactivate' );
    function moonlight_gateway_deactivate() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        unset( $_GET['activate'] );
    }
}

/**
 * Admin order page Moonlight Gateway data output.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'moonlight_gateway_admin_order_data' );
function moonlight_gateway_admin_order_data( $order ) {
    // Check if the payment method is Moonlight Gateway.
    if ( $order->get_payment_method() !== 'moonlight_gateway' ) {
        return;
    }

    // get order id
    $order_id    = $order->get_id();
    $order_total = $order->get_total();

    // Get order meta data.
    $response_text      = get_post_meta( $order_id, '_mpg_response_text', true );
    $auth_code          = get_post_meta( $order_id, '_mpg_auth_code', true );
    $transaction_id     = get_post_meta( $order_id, '_mpg_transaction_id', true );
    $moonlight_order_id = get_post_meta( $order_id, '_mpg_order_id', true );
    $order_type         = get_post_meta( $order_id, '_mpg_order_type', true );
    $response_code      = get_post_meta( $order_id, '_mpg_response_code', true );

    // Output the data.
    ?>
    <div class="form-field form-field-wide">
        <h3><?php esc_html_e( 'Moonlight Gateway Payment Details', 'mpg' ); ?></h3>
        <img src="<?php echo esc_url( PLUGIN_PUBLIC_ASSETS_URL . '/images/online-payment.png' ); ?>" alt="Moonlight Gateway"
            style="max-width: 100px;">
        <table class="wp-list-table widefat fixed striped posts" style="margin-top: 0;">
            <tbody>
                <!-- <tr>
                    <th><strong><?php // esc_html_e( 'Response Text', 'mpg' ); ?></strong></th>
                    <td>: <?php // echo esc_html( $response_text ); ?></td>
                </tr> -->
                <tr>
                    <th><strong><?php esc_html_e( 'Auth Code', 'mpg' ); ?></strong></th>
                    <td>: <?php echo esc_html( $auth_code ); ?></td>
                </tr>
                <tr>
                    <th><strong><?php esc_html_e( 'Transaction ID', 'mpg' ); ?></strong></th>
                    <td>: <?php echo esc_html( $transaction_id ); ?></td>
                </tr>
                <tr>
                    <th><strong><?php esc_html_e( 'Order ID', 'mpg' ); ?></strong></th>
                    <td>: <?php echo esc_html( $moonlight_order_id ); ?></td>
                </tr>
                <tr>
                    <th><strong><?php esc_html_e( 'Order Type', 'mpg' ); ?></strong></th>
                    <td>: <?php echo esc_html( $order_type ); ?></td>
                </tr>
                <!-- <tr>
                    <th><strong><?php // esc_html_e( 'Response Code', 'mpg' ); ?></strong></th>
                    <td>: <?php // echo esc_html( $response_code ); ?></td>
                </tr> -->
                <tr>
                    <th><strong><?php esc_html_e( 'Refund', 'mpg' ); ?></strong></th>
                    <td>: <button type="button" data-order-id="<?= esc_attr( $order_id ); ?>"
                            data-transaction-id="<?= esc_attr( $transaction_id ); ?>"
                            data-amount="<?= esc_attr( $order_total ); ?>" id="moonlight-gateway-refund"
                            class="common-btn ">
                            <span><?php esc_html_e( 'Refund', 'mpg' ); ?></span>
                            <span class="refund-spinner-loader-wrapper"></span>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>
                        <div id="toast-container"></div>
                    </th>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}
