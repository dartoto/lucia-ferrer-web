<?php
/**
 * Plugin Name:             Advanced Custom CSS for Elementor
 * Description:             Add Custom CSS to Elementor widgets and Atomic Elements without Elementor Pro, with responsive controls and live preview support.
 * Version:                 1.0.3
 * Requires at least:       6.3
 * Requires PHP:            7.4
 * Requires Plugins:        elementor
 * Author:                  Agreem Technologies
 * Author URI:              https://agreemtech.com/
 * License:                 GPLv2 or later
 * License URI:             https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:             advanced-custom-css-for-elementor
 * Elementor tested up to:  4.3.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'ACCE_VERSION', '1.0.3' );
define( 'ACCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ACCE_PLUGIN_DIR . 'includes/class-acce-atomic-support.php';
require_once ACCE_PLUGIN_DIR. 'includes/class-acce-plugin.php';


add_action( 'elementor/loaded', function () {
    new ACCE_Plugin();
} );

add_action( 'plugins_loaded', function () {

    // Elementor missing notice.
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function () {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            printf(
                '<div class="notice notice-error is-dismissible"><p><strong>%s:</strong> %s</p></div>',
                esc_html__( 'Advanced Custom CSS for Elementor', 'advanced-custom-css-for-elementor' ),
                esc_html__( 'This plugin requires "Elementor" to be installed and activated.', 'advanced-custom-css-for-elementor' )
            );
        } );
    }

    // Conflicting plugin check: admin only, so the front end is not affected.
    if ( ! is_admin() ) {
        return;
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $conflicting_plugins = [
        'custom-css-for-elementor/custom-css-for-elementor.php' => 'Custom CSS for Elementor',
    ];

    foreach ( $conflicting_plugins as $plugin_path => $plugin_name ) {
        if ( ! is_plugin_active( $plugin_path ) ) {
            continue;
        }
        add_action( 'admin_notices', function () use ( $plugin_name ) {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            printf(
                '<div class="notice notice-warning"><p><strong>%s:</strong> %s</p></div>',
                esc_html__( 'Advanced Custom CSS for Elementor', 'advanced-custom-css-for-elementor' ),
                sprintf(
                    /* translators: %s: name of the conflicting plugin. */
                    esc_html__( '"%s" may conflict with this plugin. Some features might not work as expected.', 'advanced-custom-css-for-elementor' ),
                    esc_html( $plugin_name )
                )
            );
        } );
    }
}, 20 );