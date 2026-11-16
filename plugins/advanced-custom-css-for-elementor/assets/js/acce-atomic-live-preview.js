/**
 * Live preview of ACCE Custom CSS for Elementor Atomic Elements.
 * Runs in the editor window and writes a <style> tag into the preview iframe.
 */
(function () {
    'use strict';

    if (typeof window.acceAtomicData === 'undefined') {
        return;
    }

    var STYLE_ID = 'acce-atomic-live-css';
    var cfg = window.acceAtomicData;
    var timer = null;

    function unwrap(value) {
        if (value && typeof value === 'object' && 'value' in value) {
            value = value.value;
        }
        return typeof value === 'string' ? value : '';
    }

    function sanitize(css) {
        return css
            .replace(/<\/?[a-z!][^>]*>/gi, '')
            .replace(/</g, '')
            .replace(/expression\s*\(/gi, '(')
            .replace(/(javascript|vbscript)\s*:/gi, '')
            .replace(/-moz-binding\s*:[^;}]*;?/gi, '')
            .replace(/behavior\s*:[^;}]*;?/gi, '')
            .trim();
    }

    function replaceSelector(css, selector) {
        return css.replace(/(^|[^\w.#-])selector(?![\w-])/g, function (m, prefix) {
            return prefix + selector;
        });
    }

    function readProp(container, key) {
        var settings = container && container.settings;
        if (settings && typeof settings.get === 'function') {
            return sanitize(unwrap(settings.get(key)));
        }
        var model = container && container.model;
        if (model && typeof model.get === 'function') {
            var raw = model.get('settings');
            if (raw && typeof raw.get === 'function') {
                return sanitize(unwrap(raw.get(key)));
            }
        }
        return '';
    }

    function elementCss(container, postId) {
        var id = String(container.id || '').replace(/[^A-Za-z0-9_-]/g, '');
        if (!id) {
            return '';
        }

        var desktop = readProp(container, cfg.props.desktop);
        var tablet = readProp(container, cfg.props.tablet);
        var mobile = readProp(container, cfg.props.mobile);
        if (!desktop && !tablet && !mobile) {
            return '';
        }

        var base = '.elementor-' + postId;
        var selector = ':is(' + base + ' [data-interaction-id="' + id + '"], ' + base + ' [data-id="' + id + '"])';
        var css = '';

        if (desktop) {
            css += replaceSelector(desktop, selector) + '\n';
        }
        if (tablet) {
            css += '@media (max-width: ' + parseInt(cfg.breakpoints.tablet, 10) + 'px) {\n' + replaceSelector(tablet, selector) + '\n}\n';
        }
        if (mobile) {
            css += '@media (max-width: ' + parseInt(cfg.breakpoints.mobile, 10) + 'px) {\n' + replaceSelector(mobile, selector) + '\n}\n';
        }
        return css;
    }

    function collect(container, postId, out) {
        if (!container) {
            return;
        }
        var css = elementCss(container, postId);
        if (css) {
            out.push(css);
        }
        var children = container.children || [];
        for (var i = 0; i < children.length; i++) {
            collect(children[i], postId, out);
        }
    }

    function buildCss(rootContainer, postId) {
        var out = [];
        collect(rootContainer, postId, out);
        return out.join('\n');
    }

    function apply() {
        try {
            var doc = window.elementor && elementor.documents && elementor.documents.getCurrent();
            if (!doc || !doc.container) {
                return;
            }
            var frame = elementor.$preview && elementor.$preview[0];
            var previewDoc = frame && frame.contentDocument;
            if (!previewDoc || !previewDoc.head) {
                return;
            }

            var style = previewDoc.getElementById(STYLE_ID);
            if (!style) {
                style = previewDoc.createElement('style');
                style.id = STYLE_ID;
                previewDoc.head.appendChild(style);
            }
            style.textContent = buildCss(doc.container, doc.id);
        } catch (e) {
            // Preview refresh is best-effort; ignore errors.
        }
    }

    function schedule() {
        clearTimeout(timer);
        timer = setTimeout(apply, 60);
    }

    function isRelevantCommand(command) {
        return typeof command === 'string' &&
            (command.indexOf('document/') === 0 || command.indexOf('editor/documents/') === 0);
    }

    var bound = false;

    function bind() {
        if (bound) {
            schedule();
            return;
        }
        bound = true;

        // Elementor dispatches this window event for every command, including the
        // internal "document/elements/set-settings" that atomic elements use.
        window.addEventListener('elementor/commands/run/after', function (e) {
            var command = e && e.detail && e.detail.command;
            if (isRelevantCommand(command)) {
                schedule();
            }
        });

        // Older / classic command hook, kept as a fallback.
        if (window.$e && $e.commands && typeof $e.commands.on === 'function') {
            $e.commands.on('run:after', function (component, command) {
                if (isRelevantCommand(command)) {
                    schedule();
                }
            });
        }

        // The preview iframe was (re)loaded: write the <style> tag again.
        if (window.elementor && typeof elementor.on === 'function') {
            elementor.on('preview:loaded', schedule);
        }

        schedule();
    }

    // This script can load before or after "elementor:init", so handle both cases.
    if (window.elementor && typeof elementor.on === 'function') {
        bind();
    }
    if (window.jQuery) {
        jQuery(window).on('elementor:init', bind);
    }

    window.acceAtomicLive = { apply: apply, buildCss: buildCss, replaceSelector: replaceSelector, sanitize: sanitize };
})();
