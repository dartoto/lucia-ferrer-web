<?php
/**
 * Custom CSS support for Elementor Atomic Elements (Elementor 4+).
 *
 * Atomic elements do not use the classic controls system. Their settings are
 * typed props ( [ '$$type' => 'string', 'value' => '...' ] ), and they are
 * extended through the "elementor/atomic-widgets/*" filters.
 *
 * Every hook here is guarded, so on Elementor versions without atomic
 * elements this class does nothing.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ACCE_Atomic_Support' ) ) {
    class ACCE_Atomic_Support {

        const PROP_DESKTOP = 'acce_css_desktop';
        const PROP_TABLET  = 'acce_css_tablet';
        const PROP_MOBILE  = 'acce_css_mobile';

        const STRING_PROP_TYPE = '\\Elementor\\Modules\\AtomicWidgets\\PropTypes\\Primitives\\String_Prop_Type';

        private $prop_class = null;
        private $done_docs  = [];
        private $pending    = [];

        public function __construct() {
            add_filter( 'elementor/atomic-widgets/props-schema', [ $this, 'add_props' ], 20 );
            add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_script' ], 20 );

            // Front-end delivery that does not depend on parse_css firing for atomic elements.
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_css' ], 30 );
            add_action( 'elementor/frontend/before_get_builder_content', [ $this, 'collect_builder_content_css' ], 10, 2 );
            add_action( 'wp_footer', [ $this, 'print_pending_css' ], 5 );
            add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ], 20 );
        }

        /* ------------------------------------------------------------------ */
        /* Helpers                                                            */
        /* ------------------------------------------------------------------ */

        public static function get_breakpoints() {
            $defaults    = [ 'tablet' => 1024, 'mobile' => 767 ];
            $breakpoints = apply_filters( 'ACCE_custom_css_breakpoints', $defaults );
            if ( ! is_array( $breakpoints ) ) {
                $breakpoints = $defaults;
            }
            return [
                'tablet' => (int) ( $breakpoints['tablet'] ?? $defaults['tablet'] ),
                'mobile' => (int) ( $breakpoints['mobile'] ?? $defaults['mobile'] ),
            ];
        }

        /**
         * Strips anything that could break out of a <style> block or run script.
         * Similar in spirit to what Elementor core does for its own custom CSS.
         */
        public static function sanitize_css( $css ) {
            if ( ! is_string( $css ) || '' === $css ) {
                return '';
            }
            $css = wp_check_invalid_utf8( $css );
            $css = preg_replace( '#</?[a-z!][^>]*>#i', '', $css );
            $css = str_replace( [ '<', "\0" ], '', $css );
            $css = preg_replace( '/expression\s*\(/i', '(', $css );
            $css = preg_replace( '/(javascript|vbscript)\s*:/i', '', $css );
            $css = preg_replace( '/-moz-binding\s*:[^;}]*;?/i', '', $css );
            $css = preg_replace( '/behavior\s*:[^;}]*;?/i', '', $css );
            return trim( $css );
        }

        /** Replaces the "selector" keyword (not when part of another word or a hyphenated name). */
        public static function replace_selector( $css, $selector ) {
            return preg_replace_callback(
                '/(?<![\w.#-])selector(?![\w-])/',
                function () use ( $selector ) {
                    return $selector;
                },
                $css
            );
        }

        /** Reads a prop value whether it is a plain string or a typed prop array. */
        private static function unwrap( $value ) {
            if ( is_array( $value ) && array_key_exists( 'value', $value ) ) {
                $value = $value['value'];
            }
            return is_string( $value ) ? $value : '';
        }

        private function get_prop_class() {
            if ( null !== $this->prop_class ) {
                return $this->prop_class;
            }

            $this->prop_class = self::STRING_PROP_TYPE;

            if ( ! class_exists( self::STRING_PROP_TYPE ) ) {
                return $this->prop_class;
            }

            try {
                $method = new ReflectionMethod( self::STRING_PROP_TYPE, 'sanitize_value' );
                $safe   = ! $method->isFinal() && ! $method->isStatic()
                    && 1 === $method->getNumberOfParameters() && ! $method->hasReturnType();
                if ( $safe ) {
                    require_once ACCE_PLUGIN_DIR . 'includes/class-acce-atomic-css-prop-type.php';
                    if ( class_exists( 'ACCE_Atomic_Css_Prop_Type', false ) ) {
                        $this->prop_class = 'ACCE_Atomic_Css_Prop_Type';
                    }
                }
            } catch ( \Throwable $e ) {
                // Fall back to the stock string type.
            }

            return $this->prop_class;
        }

        /* ------------------------------------------------------------------ */
        /* Editor: props + controls                                           */
        /* ------------------------------------------------------------------ */

        public function add_props( $schema ) {
            if ( ! is_array( $schema ) || ! class_exists( self::STRING_PROP_TYPE ) ) {
                return $schema;
            }

            try {
                $class = $this->get_prop_class();

                foreach ( [ self::PROP_DESKTOP, self::PROP_TABLET, self::PROP_MOBILE ] as $key ) {
                    if ( ! isset( $schema[ $key ] ) ) {
                        $schema[ $key ] = $class::make()->default( '' );
                    }
                }
            } catch ( \Throwable $e ) {
                // Leave Elementor's schema untouched if its internals differ from what we expect.
                return $schema;
            }

            return $schema;
        }

        public function enqueue_editor_styles() {
            wp_register_style( 'acce-atomic-editor-style', false, [], ACCE_VERSION );
            wp_enqueue_style( 'acce-atomic-editor-style' );
            wp_add_inline_style(
                'acce-atomic-editor-style',
                '.acce-css-field textarea::placeholder{color:#9a9a9a!important;opacity:1!important;font-style:italic;}'
            );
        }

        public function enqueue_editor_script() {
            wp_enqueue_script(
                'acce-atomic-live-preview',
                ACCE_PLUGIN_URL . 'assets/js/acce-atomic-live-preview.js',
                [ 'jquery' ],
                ACCE_VERSION,
                true
            );
            // Adds the ACCE Custom CSS section to the Styles tab of atomic elements.
            wp_enqueue_script(
                'acce-atomic-style-tab',
                ACCE_PLUGIN_URL . 'assets/js/acce-atomic-style-tab.js',
                [],
                ACCE_VERSION,
                true
            );
            wp_localize_script(
                'acce-atomic-style-tab',
                'acceAtomicStyleTab',
                [
                    'title'       => __( 'ACCE Custom CSS', 'advanced-custom-css-for-elementor' ),
                    'placeholder' => "selector {\n    background: #000;\n    color: #fff;\n}\n\nselector:hover {\n    opacity: .8;\n}",
                    'fields'      => [
                        [ 'bind' => self::PROP_DESKTOP, 'label' => __( 'Desktop CSS', 'advanced-custom-css-for-elementor' ) ],
                        [ 'bind' => self::PROP_TABLET,  'label' => __( 'Tablet CSS', 'advanced-custom-css-for-elementor' ) ],
                        [ 'bind' => self::PROP_MOBILE,  'label' => __( 'Mobile CSS', 'advanced-custom-css-for-elementor' ) ],
                    ],
                ]
            );
            wp_localize_script(
                'acce-atomic-live-preview',
                'acceAtomicData',
                [
                    'breakpoints' => self::get_breakpoints(),
                    'props'       => [
                        'desktop' => self::PROP_DESKTOP,
                        'tablet'  => self::PROP_TABLET,
                        'mobile'  => self::PROP_MOBILE,
                    ],
                ]
            );
        }


        /* ------------------------------------------------------------------ */
        /* Shared CSS builder + front-end fallback                            */
        /* ------------------------------------------------------------------ */

        public static function build_element_css( $settings, $post_id, $element_id ) {
            if ( ! is_array( $settings ) ) {
                return '';
            }

            $desktop = self::sanitize_css( self::unwrap( $settings[ self::PROP_DESKTOP ] ?? '' ) );
            $tablet  = self::sanitize_css( self::unwrap( $settings[ self::PROP_TABLET ] ?? '' ) );
            $mobile  = self::sanitize_css( self::unwrap( $settings[ self::PROP_MOBILE ] ?? '' ) );

            if ( '' === $desktop && '' === $tablet && '' === $mobile ) {
                return '';
            }

            $id       = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $element_id );
            // Atomic widgets render data-interaction-id on the real root tag; containers also carry data-id.
            $selector = sprintf( ':is(.elementor-%1$d [data-interaction-id="%2$s"], .elementor-%1$d [data-id="%2$s"])', (int) $post_id, $id );
            $bp       = self::get_breakpoints();
            $css      = '';

            if ( '' !== $desktop ) {
                $css .= self::replace_selector( $desktop, $selector ) . "\n";
            }
            if ( '' !== $tablet ) {
                $css .= sprintf( "@media (max-width: %dpx) {\n%s\n}\n", $bp['tablet'], self::replace_selector( $tablet, $selector ) );
            }
            if ( '' !== $mobile ) {
                $css .= sprintf( "@media (max-width: %dpx) {\n%s\n}\n", $bp['mobile'], self::replace_selector( $mobile, $selector ) );
            }

            return sprintf( "/* Start ACCE custom CSS for atomic element %s */\n%s/* End ACCE custom CSS */\n", $id, $css );
        }

        private function walk_elements( $elements, $post_id, &$css ) {
            if ( ! is_array( $elements ) ) {
                return;
            }
            foreach ( $elements as $el ) {
                if ( ! is_array( $el ) ) {
                    continue;
                }
                if ( ! empty( $el['id'] ) && ! empty( $el['settings'] ) ) {
                    $css .= self::build_element_css( $el['settings'], $post_id, $el['id'] );
                }
                if ( ! empty( $el['elements'] ) ) {
                    $this->walk_elements( $el['elements'], $post_id, $css );
                }
            }
        }

        private function is_editing() {
            if ( ! class_exists( '\\Elementor\\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
                return false;
            }
            $plugin = \Elementor\Plugin::$instance;
            return ( isset( $plugin->preview ) && $plugin->preview->is_preview_mode() )
                || ( isset( $plugin->editor ) && $plugin->editor->is_edit_mode() );
        }

        private function get_document_css( $doc_id ) {
            $doc_id    = (int) $doc_id;
            $use_cache = ! is_preview();
            $signature = md5( ACCE_VERSION . get_post_field( 'post_modified_gmt', $doc_id ) . wp_json_encode( self::get_breakpoints() ) );
            $key       = 'acce_atomic_' . $doc_id;

            if ( $use_cache ) {
                $cached = get_transient( $key );
                if ( is_array( $cached ) && isset( $cached['sig'], $cached['css'] ) && $cached['sig'] === $signature ) {
                    return $cached['css'];
                }
            }

            $css = '';
            try {
                $doc = \Elementor\Plugin::$instance->documents->get( $doc_id );
                if ( $doc && method_exists( $doc, 'get_elements_data' ) ) {
                    $this->walk_elements( $doc->get_elements_data(), $doc_id, $css );
                }
            } catch ( \Throwable $e ) {
                return '';
            }

            if ( $use_cache ) {
                set_transient( $key, [ 'sig' => $signature, 'css' => $css ], DAY_IN_SECONDS );
            }

            return $css;
        }

        /** Main post: add the CSS in <head> so there is no flash of unstyled content. */
        public function enqueue_frontend_css() {
            if ( ! is_singular() || $this->is_editing() || ! class_exists( '\\Elementor\\Plugin' ) ) {
                return;
            }
            $doc_id = (int) get_queried_object_id();
            if ( ! $doc_id || isset( $this->done_docs[ $doc_id ] ) ) {
                return;
            }
            $css = $this->get_document_css( $doc_id );
            $this->done_docs[ $doc_id ] = true;
            if ( '' === $css ) {
                return;
            }
            wp_register_style( 'acce-atomic-frontend', false, [], ACCE_VERSION );
            wp_enqueue_style( 'acce-atomic-frontend' );
            wp_add_inline_style( 'acce-atomic-frontend', $css );
        }

        /** Any other Elementor document rendered on the page (templates, loops, popups, ...). */
        public function collect_builder_content_css( $document = null, $is_excerpt = false ) {
            if ( $this->is_editing() || ! is_object( $document ) || ! method_exists( $document, 'get_main_id' ) ) {
                return;
            }
            $doc_id = (int) $document->get_main_id();
            if ( ! $doc_id || isset( $this->done_docs[ $doc_id ] ) ) {
                return;
            }
            $this->done_docs[ $doc_id ] = true;
            $css = $this->get_document_css( $doc_id );
            if ( '' !== $css ) {
                $this->pending[ $doc_id ] = $css;
            }
        }

        public function print_pending_css() {
            foreach ( $this->pending as $doc_id => $css ) {
                $handle = 'acce-atomic-css-' . (int) $doc_id;
                wp_register_style( $handle, false, [], ACCE_VERSION );
                wp_enqueue_style( $handle );
                wp_add_inline_style( $handle, $css );
            }
            $this->pending = [];
        }
    }
}
