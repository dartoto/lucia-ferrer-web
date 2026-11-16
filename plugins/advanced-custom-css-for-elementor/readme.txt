=== Advanced Custom CSS for Elementor ===
Contributors: agreem2016
Tags: custom css, custom css for elementor, elementor css, elementor custom css, elementor widgets
Requires at least: 6.3
Tested up to: 7.1
Stable tag: 1.0.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add custom CSS to Elementor widgets and Atomic Elements without Elementor Pro. Responsive styles, selector keyword and live preview.

== Description ==
Custom CSS for Elementor lets you add custom CSS to every Elementor widget without requiring Elementor Pro. Write widget-specific CSS using the familiar `selector` keyword, preview changes instantly, and create responsive styles for desktop, tablet, and mobile—all directly inside the Elementor editor.

If you are searching for an Elementor CSS plugin, Elementor free custom CSS support, or a way to add CSS to Elementor widgets without upgrading to Pro, this plugin is built for exactly that workflow.

With this plugin enabled, you get a dedicated Custom CSS area in the **Advanced** tab for each widget. You can keep styles local to a single widget, avoid global CSS conflicts, and maintain cleaner design control while editing pages.

Atomic Elements support requires Elementor 4.0 or later; classic widgets work on earlier versions.

== Why Use Custom CSS for Elementor? ==
- Add Custom CSS without Elementor Pro.
- Style individual Elementor widgets without affecting the whole page.
- Write widget-level CSS using Elementor `selector` syntax.
- Create responsive CSS for Desktop, Tablet, and Mobile.
- See live preview updates while editing in Elementor.
- Keep your setup lightweight with no extra configuration.
- Works with both Elementor Free and Elementor Pro.
- Supports Elementor Atomic Elements (Elementor 4+) as well as classic widgets.

== Features ==
- Add Custom CSS to Elementor widgets from the Advanced tab.
- Create widget-specific CSS without impacting unrelated elements.
- Use it as a free alternative when you only need Elementor Pro Custom CSS behavior.
- Separate responsive CSS fields for Desktop, Tablet, and Mobile.
- Supports Elementor `selector` syntax for precise targeting.
- Instant live preview inside Elementor editor.
- Lightweight plugin with zero settings pages.
- Compatible with modern WordPress and Elementor versions.

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from the **Plugins** screen in WordPress.
3. Edit a page with Elementor.
4. Select any widget and open **Advanced > ACCE Custom CSS**. For Atomic Elements (Heading, Div Block, etc.), open the **Style** tab and find **ACCE Custom CSS**.
5. Add CSS using `selector`.

== Usage ==
1. Open any page in Elementor.
2. Click a widget you want to style.
3. Go to **Advanced > ACCE Custom CSS**.
4. Add CSS in Desktop, Tablet, or Mobile tabs.
5. Use `selector` to target the current widget only.

Example 1:

selector {
    background: #000;
    color: #fff;
}

Example 2:

selector:hover {
    transform: scale(1.05);
}

Example 3:

selector img {
    border-radius: 20px;
}

== Use Cases ==
- Quickly style CTA buttons on one landing page section.
- Apply hover effects to selected cards only.
- Create mobile-specific spacing for hero widgets.
- Add custom border, shadow, and typography tweaks per widget.
- Prototype CSS ideas in Elementor before moving to theme stylesheets.

== Developer Notes ==
Default breakpoints are Tablet: 1024px and Mobile: 767px.

You can override breakpoints using this filter:

add_filter( 'ACCE_custom_css_breakpoints', function( $defaults ) {
    return [
        'tablet' => 900,
        'mobile' => 600,
    ];
} );

== Frequently Asked Questions ==
= Can I add Custom CSS in Elementor Free? =
Yes. This plugin adds Custom CSS functionality to Elementor Free so you can style individual widgets without Elementor Pro.

= Is this a free alternative to Elementor Pro Custom CSS? =
Yes. If you mainly need widget-level Custom CSS, this plugin provides that workflow for free.

= Does it work with Elementor Atomic Elements? =
Yes. Atomic Elements (Elementor 4+) get an **ACCE Custom CSS** section in the **Style** tab with Desktop, Tablet and Mobile fields. The `selector` keyword works the same way.

= Does it work with every Elementor widget? =
It is designed to add a Custom CSS panel to Elementor widgets so you can style each widget directly from the editor.

= Does it work with Elementor Pro? =
Yes. It is compatible with both Elementor Free and Elementor Pro.

= Can I write responsive CSS? =
Yes. Separate CSS editors are available for Desktop, Tablet, and Mobile styles.

= Does it support the `selector` keyword? =
Yes. Use `selector` in your rules and the plugin maps it to the current widget wrapper.

= Will my CSS affect other widgets? =
No, if you use `selector` correctly your CSS stays scoped to the current Elementor widget.

= Does it slow down Elementor? =
The plugin is lightweight and focused. It does not add heavy admin interfaces or unnecessary modules.

= Can developers customize breakpoints? =
Yes. Developers can change tablet and mobile breakpoints via the `ACCE_custom_css_breakpoints` filter.

= Do I need coding knowledge? =
Basic CSS knowledge is enough. You do not need advanced PHP or JavaScript experience to use it.

= Can I use this on client websites? =
Yes. It is suitable for freelancers, agencies, and site owners who need quick Elementor widget styling control.

= Where do I find the CSS panel? =
Edit a widget in Elementor, open the **Advanced** tab, then find **ACCE Custom CSS**.

== Troubleshooting ==
= My CSS is not applying. What should I check? =
- Make sure you used `selector` for widget-scoped rules.
- Confirm your CSS syntax is valid.
- Clear Elementor and site cache after changes.
- Check whether another CSS plugin is overriding the same styles.

= Responsive styles look different than expected. Why? =
Check your media query logic and verify custom breakpoints if your project overrides defaults.

== License ==
This plugin is licensed under the GPLv2 or later.

== Changelog ==
= 1.0.3 =
* New: Custom CSS support for Elementor Atomic Elements (Elementor 4+), with Desktop, Tablet and Mobile fields and live preview.
* Improved: Classic live preview now respects the `ACCE_custom_css_breakpoints` filter.
* Improved: Custom CSS is sanitized to remove HTML tags and legacy script vectors.

= 1.0.2 =
* Confirmed compatibility with WordPress 7.1.

= 1.0.1 =
* Improved Custom CSS for Elementor compatibility with WordPress 7.0.
* Improved PHP 8.2+ compatibility.
* Improved breakpoint handling for responsive Elementor widget styling.

= 1.0 =
* Initial release of Custom CSS for Elementor with responsive widget CSS, `selector` support, and live preview.

== Upgrade Notice ==
= 1.0.3 =
Adds Custom CSS support for Elementor Atomic Elements (Elementor 4+).

= 1.0.2 =
Confirmed compatibility with WordPress 7.1.

= 1.0.1 =
Recommended update for improved WordPress 7.0 compatibility, better PHP 8.2+ support, and improved responsive breakpoint handling.

= 1.0 =
First release — adds responsive Custom CSS controls to Elementor widgets.
