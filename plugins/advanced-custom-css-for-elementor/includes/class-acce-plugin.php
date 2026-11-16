<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ACCE_Plugin' ) ) {
    class ACCE_Plugin {
        private $ACCE_breakpoints = [
            'tablet' => 1024,
            'mobile' => 767,
        ];
        public function __construct() {
            add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'ACCE_enqueue_scripts' ], 20 );
            add_action( 'elementor/element/after_section_end', [ $this, 'ACCE_add_custom_css_controls' ], 20, 2 );
            add_action( 'elementor/element/parse_css', [ $this, 'ACCE_add_post_css' ], 20, 2 );
            add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'ACCE_enqueue_editor_styles' ] );

            // Elementor Atomic Elements (Elementor 4+).
            if ( class_exists( 'ACCE_Atomic_Support' ) ) {
                new ACCE_Atomic_Support();
            }
        }

        /**
         * Breakpoints used for the tablet/mobile CSS, filterable via ACCE_custom_css_breakpoints.
         */
        public function ACCE_get_breakpoints() {
            $breakpoints = apply_filters( 'ACCE_custom_css_breakpoints', $this->ACCE_breakpoints );
            if ( ! is_array( $breakpoints ) ) {
                $breakpoints = $this->ACCE_breakpoints;
            }
            return [
                'tablet' => (int) ( $breakpoints['tablet'] ?? $this->ACCE_breakpoints['tablet'] ),
                'mobile' => (int) ( $breakpoints['mobile'] ?? $this->ACCE_breakpoints['mobile'] ),
            ];
        }

        /**
         * Enqueues a small editor-only stylesheet that lightens the
         * placeholder text color in the ACCE Custom CSS fields, so it
         * reads clearly as a hint rather than as real entered text.
         */
        public function ACCE_enqueue_editor_styles() {
            wp_register_style( 'acce-editor-style', false, [], ACCE_VERSION );
            wp_enqueue_style( 'acce-editor-style' );

            $css = '
                .elementor-control-acce_custom_css_desktop textarea::placeholder,
                .elementor-control-acce_custom_css_tablet textarea::placeholder,
                .elementor-control-acce_custom_css_mobile textarea::placeholder {
                    color: #9a9a9a !important;
                    opacity: 1 !important;
                    font-style: italic;
                }
            ';

            wp_add_inline_style( 'acce-editor-style', $css );
        }

        public function ACCE_enqueue_scripts() {
            if ( \Elementor\Plugin::$instance->preview->is_preview_mode() || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                wp_enqueue_script(
                    'acce-live-preview-script',
                    ACCE_PLUGIN_URL . 'assets/js/acce-live-preview-script.js',
                    array( 'elementor-frontend' ),
                    ACCE_VERSION,
                    true
                );
                wp_localize_script(
                    'acce-live-preview-script',
                    'acceModelData',
                    [
                        'postID'      => absint( get_the_ID() ),
                        'breakpoints' => $this->ACCE_get_breakpoints(),
                    ]
                );
            }
        }

        public function ACCE_add_custom_css_controls( $element, $section_id ) {
            if ( $section_id !== '_section_style' ) {
                return;
            }

            $element->start_controls_section(
                'acce_custom_css_section',
                [
                    'label' => __( 'ACCE Custom CSS', 'advanced-custom-css-for-elementor' ),
                    'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
                ]
            );

            $element->add_control(
                'acce_custom_css_tabs',
                [
                    'type'            => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'             => '<strong>' . __( 'Use "selector" to target this widget. CSS will be rendered per device.', 'advanced-custom-css-for-elementor' ) . '</strong>',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );

            $element->start_controls_tabs( 'acce_custom_css_device_tabs' );

            // === Desktop Tab ===
            $element->start_controls_tab(
                'acce_custom_css_tab_desktop',
                [
                    'label' => '<i class="eicon-device-desktop"></i>',
                ]
            );
            $element->add_control(
                'acce_custom_css_desktop',
                [
                    'type'        => \Elementor\Controls_Manager::TEXTAREA,
                    'label'      => esc_html__( 'Desktop CSS', 'advanced-custom-css-for-elementor' ),
                    'rows'       => 10,
                    'placeholder' => "selector {\n    background: #000;\n    color: #fff;\n}\n\nselector:hover {\n    transform: scale(1.05);\n}\n\nselector img {\n    border-radius: 20px;\n}",
                ]
            );
            $element->end_controls_tab();

            // === Tablet Tab ===
            $element->start_controls_tab(
                'acce_custom_css_tab_tablet',
                [
                    'label' => '<i class="eicon-device-tablet"></i>',
                ]
            );
            $element->add_control(
                'acce_custom_css_tablet',
                [
                    'type'        => \Elementor\Controls_Manager::TEXTAREA,
                    'label'      => esc_html__( 'Tablet CSS', 'advanced-custom-css-for-elementor' ),
                    'rows'       => 10,
                    'placeholder' => "selector {\n    background: #000;\n    color: #fff;\n}\n\nselector:hover {\n    transform: scale(1.05);\n}\n\nselector img {\n    border-radius: 20px;\n}",
                ]
            );
            $element->end_controls_tab();

            // === Mobile Tab ===
            $element->start_controls_tab(
                'acce_custom_css_tab_mobile',
                [
                    'label' => '<i class="eicon-device-mobile"></i>',
                ]
            );
            $element->add_control(
                'acce_custom_css_mobile',
                [
                    'type'        => \Elementor\Controls_Manager::TEXTAREA,
                    'label'      => esc_html__( 'Mobile CSS', 'advanced-custom-css-for-elementor' ),
                    'rows'       => 10,
                    'placeholder' => "selector {\n    background: #000;\n    color: #fff;\n}\n\nselector:hover {\n    transform: scale(1.05);\n}\n\nselector img {\n    border-radius: 20px;\n}",
                ]
            );
            $element->end_controls_tab();

            $element->end_controls_tabs();
            $element->end_controls_section();
        }

        public function ACCE_add_post_css( $post_css, $element ) {
            if ( $post_css instanceof \Elementor\Core\Files\CSS\Dynamic_CSS ) {
                return;
            }

            $settings = $element->get_settings();

            $devices = [
                'desktop' => ACCE_Atomic_Support::sanitize_css( $settings['acce_custom_css_desktop'] ?? '' ),
                'tablet'  => ACCE_Atomic_Support::sanitize_css( $settings['acce_custom_css_tablet'] ?? '' ),
                'mobile'  => ACCE_Atomic_Support::sanitize_css( $settings['acce_custom_css_mobile'] ?? '' ),
            ];

            if ( '' === implode( '', $devices ) ) {
                return;
            }

            $post_id    = (int) $post_css->get_post_id();   // not get_the_ID()
            $element_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $element->get_id() );
            $selector   = ".elementor-{$post_id} .elementor-element.elementor-element-{$element_id}";
            $bp         = $this->ACCE_get_breakpoints();
            $css        = '';

            if ( '' !== $devices['desktop'] ) {
                $css .= ACCE_Atomic_Support::replace_selector( $devices['desktop'], $selector ) . "\n";
            }
            if ( '' !== $devices['tablet'] ) {
                $css .= sprintf( "@media (max-width: %dpx) {\n%s\n}\n", $bp['tablet'], ACCE_Atomic_Support::replace_selector( $devices['tablet'], $selector ) );
            }
            if ( '' !== $devices['mobile'] ) {
                $css .= sprintf( "@media (max-width: %dpx) {\n%s\n}\n", $bp['mobile'], ACCE_Atomic_Support::replace_selector( $devices['mobile'], $selector ) );
            }

            $post_css->get_stylesheet()->add_raw_css(
                sprintf( "/* Start ACCE custom CSS for %s */\n%s/* End ACCE custom CSS */\n", esc_html( $element->get_name() ), $css )
            );
        }
    }
}
?>