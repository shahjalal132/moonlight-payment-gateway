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
        $gateways[] = 'NMI_Konnektive_Gateway';
        return $gateways;
    }

    /**
     * moonlight gateway init
     */
    add_action( 'plugins_loaded', 'nmi_moonlight_gateway_plugin_activation' );
    function nmi_moonlight_gateway_plugin_activation() {

        class NMI_Konnektive_Gateway extends WC_Payment_Gateway {

            public function __construct() {

                // Plugin base information
                $this->id                 = 'nmi_konnektive'; // Unique ID for the payment gateway
                $this->icon               = PLUGIN_PUBLIC_ASSETS_URL . '/images/online-payment.png'; // Payment gateway icon
                $this->method_title       = esc_html__( "NMI Konnektive", "mpg" ); // Gateway title in admin panel
                $this->method_description = esc_html__( "NMI Konnektive Payment Gateway Options", "mpg" ); // Description in admin panel
                $this->has_fields         = true; // Set to true to display custom fields during checkout

                // Load the settings
                $this->init_settings();

                // Retrieve gateway settings
                $this->title       = $this->get_option( 'title', 'NMI Konnektive Payment' ); // Payment title displayed to users
                $this->description = $this->get_option( 'description', 'NMI Konnektive Payment Gateway' ); // Description for users
                $this->enabled     = $this->get_option( 'enabled' ); // Check if the gateway is enabled
                $this->testmode    = 'yes' === $this->get_option( 'testmode' ); // Check if test mode is enabled

                // Define form fields for gateway settings
                $this->moonlight_gateway_options_fields();

                // Hook to save admin settings
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            /**
             * Define NMI Konnektive Gateway Options Fields
             * Displayed in WooCommerce > Settings > Payment Methods
             */
            public function moonlight_gateway_options_fields() {

                $this->form_fields = array(
                    'testmode'                => array(
                        'title'       => 'Test mode',
                        'label'       => 'Enable Test Mode',
                        'type'        => 'checkbox',
                        'description' => 'Place the payment gateway in test mode using test API keys.',
                        'desc_tip'    => true,
                    ),
                    'enabled'                 => array(
                        'title'       => 'Enable/Disable',
                        'label'       => 'Enable NMI Konnektive Gateway',
                        'type'        => 'checkbox',
                        'description' => 'Set to "yes" to enable this payment gateway.',
                        'default'     => 'yes',
                        'desc_tip'    => true,
                    ),
                    'title'                   => array(
                        'title'       => 'Title',
                        'type'        => 'text',
                        'description' => 'This controls the title which the user sees during checkout.',
                        'desc_tip'    => true,
                    ),
                    'description'             => array(
                        'title'       => 'Description',
                        'type'        => 'textarea',
                        'description' => 'This controls the description which the user sees during checkout.',
                        'desc_tip'    => true,
                    ),
                    'konnektive_api_login_id' => array(
                        'type'        => 'text',
                        'title'       => 'Login ID',
                        'description' => 'Konnektive API Login ID provided by the API provider.',
                        'placeholder' => 'Enter Konnektive API Login ID',
                        'desc_tip'    => true,
                    ),
                    'konnektive_api_password' => array(
                        'title'       => 'Password',
                        'type'        => 'password',
                        'description' => 'API Login Password provided by the API provider.',
                        'placeholder' => 'Enter Konnektive API Password',
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
            }
        }

    }

} else {
    /**
     * Admin Notice
     */
    add_action( 'admin_notices', 'nmi_konnektive_gateway_admin_notice__error' );
    function nmi_konnektive_gateway_admin_notice__error() {
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
    add_action( 'admin_init', 'nmi_konnektive_gateway_deactivate' );
    function nmi_konnektive_gateway_deactivate() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        unset( $_GET['activate'] );
    }
}