jQuery(window).on('elementor/frontend/init', function () {

    /**
     * Injects custom CSS into the Elementor live preview (editor mode).
     * Handles responsive rules and replaces "selector" with actual element selector.
     *
     * @param {string} css - The original CSS from Elementor.
     * @param {object} context - The current widget/document context.
     * @returns {string} Modified CSS with custom rules.
     */
    function ACCE_addCustomCSS(css, context) {
        if (!context) return;

        const model = context.model;
        const settings = model.get('settings');

        // Fetch user-defined CSS from element settings
        const customCssDesktop = settings.get('acce_custom_css_desktop') || '';
        const customCssTablet  = settings.get('acce_custom_css_tablet')  || '';
        const customCssMobile  = settings.get('acce_custom_css_mobile')  || '';

        let customCss = '';

        // Append desktop CSS
        if (customCssDesktop) {
            customCss += customCssDesktop;
        }

        // Append tablet CSS inside media query
        if (customCssTablet) {
            customCss += ` @media (max-width: ${acceModelData.breakpoints.tablet}px) { ${customCssTablet} }`;
        }

        // Append mobile CSS inside media query
        if (customCssMobile) {
            customCss += ` @media (max-width: ${acceModelData.breakpoints.mobile}px) { ${customCssMobile} }`;
        }

        if (!customCss) return css; // No custom CSS, return original

        // Generate the element selector
        let selector = `.elementor-${acceModelData.postID} .elementor-element.elementor-element-${model.get('id')}`;

        // If it's a document (page/post), use the document wrapper selector
        if (model.get('elType') === 'document') {
            selector = elementor.config.document.settings.cssWrapperSelector;
        }

        // Replace all "selector" placeholders with actual selector
        css += customCss.replaceAll('selector', selector);

        return css;
    }

    // Hook into Elementor's live editor styling pipeline
    if (typeof elementor !== 'undefined') {
        elementor.hooks.addFilter('editor/style/styleText', ACCE_addCustomCSS);
    }

});