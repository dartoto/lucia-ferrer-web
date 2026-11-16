<?php
/**
 * String prop type that keeps CSS intact.
 *
 * Elementor's stock String_Prop_Type runs values through a text sanitizer that
 * can mangle CSS (for example stripping "%20" inside url()). This subclass
 * sanitizes with the plugin's own CSS sanitizer instead. It is only loaded when
 * ACCE_Atomic_Support confirms the parent method still has the expected shape.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ACCE_Atomic_Css_Prop_Type', false ) ) {
    class ACCE_Atomic_Css_Prop_Type extends \Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type {
        public function sanitize_value( $value ) {
            return ACCE_Atomic_Support::sanitize_css( $value );
        }
    }
}
