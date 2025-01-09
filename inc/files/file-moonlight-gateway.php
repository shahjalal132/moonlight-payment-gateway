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

                // Hook to display custom thank you message
                add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'moonlight_gateway_thankyou_page' ) );
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
                    'user_name'         => array(
                        'title'       => 'User Name',
                        'type'        => 'text',
                        'description' => 'API Login User provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'password'          => array(
                        'title'       => 'Password',
                        'type'        => 'text',
                        'description' => 'API Login Password provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'test_security_key' => array(
                        'title'       => 'Test Security Key',
                        'type'        => 'text',
                        'description' => 'API Test Security Key provided by the API provider.',
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'live_security_key' => array(
                        'title'       => 'Live Security Key',
                        'type'        => 'text',
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
                    <p class="mpg-mb-0"><?php esc_html_e( 'You are currently in Test Mode. No real transactions will be processed.', 'mpg' ); ?></p>
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
                            <td><input class="mpg-card-input-field" type="text" name="mpg_expiry_date" id="mpg_expiry_date" placeholder="MM/YY"></td>
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
                            <td><input class="mpg-card-input-field" type="text" name="mpg_expiry_date" id="mpg_expiry_date" placeholder="MM/YY"></td>
                        </tr>
                    </table>
                    <?php
                }
            }

            /**
             * Process the payment and return the result.
             *
             * @param int $order_id The WooCommerce order ID.
             * @return array Payment result with success or failure details.
             */
            public function process_payment( $order_id ) {
                // Payment processing logic should be implemented here
            }

            /**
             * Display a custom thank you message after a successful order.
             *
             * @return string Custom thank you message or default WooCommerce message.
             */
            public function moonlight_gateway_thankyou_page() {
                $order_id = get_query_var( 'order-received' );
                $order    = new WC_Order( $order_id );
                if ( $order->get_payment_method() == $this->id ) {
                    $thankyou = $this->instructions;
                    return $thankyou;
                } else {
                    return esc_html__( 'Thank you. Your order has been received.', "mpg" );
                }
            }
        }

    }

    /**
     * Admin order page bKash data output
     */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'moonlight_gateway_admin_order_data' );
    function moonlight_gateway_admin_order_data( $order ) {

        if ( $order->get_payment_method() != 'softtech_bkash' )
            return;

        // get order id
        $order_id = $order->get_id();

        // $number = ( get_post_meta( $_GET['post'], '_bkash_number', true ) ) ? get_post_meta( $_GET['post'], '_bkash_number', true ) : '';
        $number = get_post_meta( $order_id, '_bkash_number', true ) ?? '';

        // $transaction = ( get_post_meta( $_GET['post'], '_bkash_transaction', true ) ) ? get_post_meta( $_GET['post'], '_bkash_transaction', true ) : '';
        $transaction = get_post_meta( $order_id, '_bkash_transaction', true ) ?? '';

        ?>
        <div class="form-field form-field-wide">
            <img src='<?php echo plugins_url( "images/bkash.png", __FILE__ ); ?>' alt="bKash">
            <table class="wp-list-table widefat fixed striped posts">
                <tbody>
                    <tr>
                        <th><strong><?php esc_html_e( 'bKash No.', 'mpg' ); ?></strong></th>
                        <td>: <?php echo esc_attr( $number ); ?></td>
                    </tr>
                    <tr>
                        <th><strong><?php esc_html_e( 'Transaction ID', 'mpg' ); ?></strong></th>
                        <td>: <?php echo esc_attr( $transaction ); ?></td>

                    </tr>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * Order review page bKash data output
     */
    add_action( 'woocommerce_order_details_after_customer_details', 'moonlight_gateway_additional_info_order_review_fields' );
    function moonlight_gateway_additional_info_order_review_fields( $order ) {

        if ( $order->get_payment_method() != 'softtech_bkash' )
            return;

        global $wp;

        // Get the order ID
        $order_id = absint( $wp->query_vars['order-received'] );

        $number      = ( get_post_meta( $order_id, '_bkash_number', true ) ) ? get_post_meta( $order_id, '_bkash_number', true ) : '';
        $transaction = ( get_post_meta( $order_id, '_bkash_transaction', true ) ) ? get_post_meta( $order_id, '_bkash_transaction', true ) : '';

        ?>
        <table>
            <tr>
                <th><?php esc_html_e( 'bKash No:', 'mpg' ); ?></th>
                <td><?php echo esc_attr( $number ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Transaction ID:', 'mpg' ); ?></th>
                <td><?php echo esc_attr( $transaction ); ?></td>
            </tr>
        </table>
        <?php

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
