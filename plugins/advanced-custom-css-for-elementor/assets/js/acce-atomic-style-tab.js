/**
 * ACCE Custom CSS: section in the Styles tab of Atomic Elements.
 *
 * Uses Elementor's own extension point (injectIntoStyleTab) from the
 * editing-panel package. The three textareas are bound to the element's
 * acce_css_desktop / acce_css_tablet / acce_css_mobile props, so the values are
 * stored on the element exactly as before (copy, duplicate, templates and
 * export keep working).
 */
(function () {
    'use strict';

    var cfg = window.acceAtomicStyleTab;
    if (!cfg || !cfg.fields) {
        return;
    }

    var INJECTION_ID = 'acce-custom-css';
    var MAX_WAIT_MS = 15000;
    var STEP_MS = 250;
    var waited = 0;

    function getCreateElement() {
        if (window.React && typeof window.React.createElement === 'function') {
            return window.React.createElement;
        }
        if (window.wp && window.wp.element && typeof window.wp.element.createElement === 'function') {
            return window.wp.element.createElement;
        }
        return null;
    }

    function getPackages() {
        var v2 = window.elementorV2;
        if (!v2) {
            return null;
        }
        var panel = v2.editorEditingPanel;
        var controls = v2.editorControls;
        var h = getCreateElement();

        if (
            !h || !panel || !controls ||
            typeof panel.injectIntoStyleTab !== 'function' ||
            !panel.StyleTabSection || !panel.SettingsField || !panel.SectionContent ||
            !controls.TextAreaControl
        ) {
            return null;
        }

        return { h: h, panel: panel, controls: controls };
    }

    function register(pkg) {
        var h = pkg.h;
        var StyleTabSection = pkg.panel.StyleTabSection;
        var SettingsField = pkg.panel.SettingsField;
        var SectionContent = pkg.panel.SectionContent;
        var TextAreaControl = pkg.controls.TextAreaControl;
        var Label = pkg.controls.ControlFormLabel || 'label';

        function Fields() {
            return h(
                SectionContent,
                null,
                cfg.fields.map(function (field) {
                    return h(
                        'div',
                        { key: field.bind, className: 'acce-css-field' },
                        h(Label, { sx: { display: 'block', mb: 1 } }, field.label),
                        h(
                            SettingsField,
                            { bind: field.bind, propDisplayName: field.label },
                            h(TextAreaControl, { placeholder: cfg.placeholder, ariaLabel: field.label })
                        )
                    );
                })
            );
        }

        function AcceSection() {
            return h(StyleTabSection, {
                section: { name: 'ACCE Custom CSS', title: cfg.title, component: Fields },
                fields: []
            });
        }

        pkg.panel.injectIntoStyleTab({
            id: INJECTION_ID,
            component: AcceSection,
            options: { overwrite: true }
        });
    }

    function boot() {
        var pkg = getPackages();
        if (pkg) {
            try {
                register(pkg);
            } catch (e) {
                // Section could not be added; the editor keeps working without it.
            }
            return;
        }

        // The Elementor packages may not be ready yet; retry for a short time.
        waited += STEP_MS;
        if (waited <= MAX_WAIT_MS) {
            window.setTimeout(boot, STEP_MS);
        }
        // Elementor without a Styles-tab slot: nothing to do (classic editor, or an older Elementor).
    }

    boot();
})();
