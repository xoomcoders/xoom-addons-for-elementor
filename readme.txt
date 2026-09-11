=== Xoom Addons for Elementor ===
Contributors: xoomaddons
Tags: elementor, elementor addons, elementor widgets, page builder, performance
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A performance-first Elementor addons plugin with a modern dashboard, per-widget toggles and conditional asset loading.

== Description ==

Xoom Addons for Elementor gives you a clean, commercial-grade control panel for everything the plugin adds to Elementor.

**A dashboard built for decisions, not decoration**

* Overview of total, active and inactive widgets and extensions.
* One-click quick actions to enable or disable whole component sets.
* System status panel showing WordPress, PHP, Elementor and licence state.

**Per-widget control**

* Enable or disable any widget with a single toggle — changes save instantly.
* Search across titles, descriptions and keywords.
* Filter by category, status (active/inactive) and package (Free/Pro).
* Bulk enable or disable everything currently visible.

**Performance first**

* Disabled widgets are never loaded, so their PHP, CSS and JS cost nothing.
* Shared widget CSS and JS are registered on demand and printed by Elementor
  only on pages that actually render a Xoom widget.
* Optional script deferral and WordPress emoji script removal.

**Extensions**

Non-widget features such as Custom CSS, Post Duplicator and Reading Progress
Bar are managed from the same dashboard with the same toggles.

= Free widgets included =

* Heading
* Button
* Icon Box
* Info List
* Counter

Free extensions:

* Custom CSS
* Post Duplicator
* Reading Progress Bar

= Extending =

The widget and extension catalogues are filterable, so another plugin can add
its own components without this plugin knowing about them:

* `xoom_addons_widgets`
* `xoom_addons_modules`
* `xoom_addons_widget_categories`
* `xoom_addons_loaded`

== Installation ==

1. Upload the `xoom-addons-for-elementor` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Install and activate Elementor if you have not already.
4. Open **Xoom Addons** in the admin menu to configure your widgets.

== Frequently Asked Questions ==

= Does it work without Elementor? =

The dashboard stays available, but no widgets are registered until Elementor
is installed and activated.

= Will disabling widgets slow down my site? =

No — it is the opposite. Disabled widgets are never loaded, so their code and
assets never reach your pages.

= How do I add my own widget? =

Add an entry to `includes/registries/class-widget-registry.php` and drop the
widget class into `widgets/`. Full instructions are in the plugin README.

== Changelog ==

= 1.0.0 =
* Initial release.
* Component-based dashboard with widget and extension management.
* Search, category, status and package filters with bulk actions.
* Conditional frontend asset loading and performance settings.
* Filterable widget, extension and category registries for add-on plugins.
