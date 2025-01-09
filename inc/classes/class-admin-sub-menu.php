<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Program_Logs;
use BOILERPLATE\Inc\Traits\Singleton;

class Admin_Sub_Menu {

    use Singleton;
    use Program_Logs;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'admin_menu', [ $this, 'register_admin_sub_menu' ] );
        add_filter( 'plugin_action_links_' . PLUGIN_BASE_NAME, [ $this, 'add_plugin_action_links' ] );

        // save api credentials
        add_action( 'wp_ajax_save_credentials', [ $this, 'save_api_credentials' ] );
        add_action( 'wp_ajax_save_options', [ $this, 'save_options' ] );

        // mpg_refund
        add_action( 'wp_ajax_mpg_refund', [ $this, 'mpg_refund' ] );
    }

    public function mpg_refund() {

        $transactionid = sanitize_text_field( $_POST['transaction_id'] );
        $amount        = sanitize_text_field( $_POST['amount'] );
        $security_key  = get_option( 'security_key' );
        $api_url       = "https://moonlight.transactiongateway.com/api/transact.php";

        $query = "";
        // Login Information
        $query .= "security_key=" . urlencode( $security_key ) . "&";
        // Transaction Information
        $query .= "transactionid=" . urlencode( $transactionid ) . "&";
        if ( $amount > 0 ) {
            $query .= "amount=" . urlencode( number_format( $amount, 2, ".", "" ) ) . "&";
        }
        $query .= "type=refund";

        // send post request
        $response = wp_remote_post( $api_url, [
            'body'    => $query,
            'timeout' => 60,
        ] );

        $this->put_program_logs( "Refund Response: " . json_encode( $response ) );

        if ( is_wp_error( $response ) ) {
            // wp_send_json_error( $response->get_error_message() );
            wp_send_json_error( "Refund failed" );
        }

        $body = wp_remote_retrieve_body( $response );

        if ( $body ) {
            wp_send_json_success( "Refund successfully" );
        }

    }

    public function save_api_credentials() {

        $api_url      = sanitize_text_field( $_POST['api_url'] );
        $api_key      = sanitize_text_field( $_POST['api_key'] );
        $security_key = sanitize_text_field( $_POST['security_key'] );

        if ( empty( $api_url ) || empty( $api_key ) ) {
            wp_send_json_error( 'An error occurred! Please fill all the fields.' );
        }

        update_option( 'api_url', $api_url );
        update_option( 'api_key', $api_key );
        update_option( 'security_key', $security_key );

        wp_send_json_success( 'Credentials saved successfully!' );
        die();
    }

    public function save_options() {

        $option1 = sanitize_text_field( $_POST['option1'] );
        $option2 = sanitize_text_field( $_POST['option2'] );

        update_option( 'option1', $option1 );
        update_option( 'option2', $option2 );

        wp_send_json_success( 'Options saved successfully!' );
        die();
    }

    function add_plugin_action_links( $links ) {
        $settings_link = '<a href="admin.php?page=moonlight-gateway">' . __( 'Settings', 'mpg' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    public function register_admin_sub_menu() {
        add_submenu_page(
            'options-general.php',
            'Moonlight Gateway Settings',
            'Moonlight Gateway Settings',
            'manage_options',
            'moonlight-gateway',
            [ $this, 'menu_callback_html' ],
        );
    }

    public function menu_callback_html() {
        include_once PLUGIN_BASE_PATH . '/templates/template-admin-sub-menu.php';
    }

}