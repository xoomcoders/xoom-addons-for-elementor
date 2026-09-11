# Xoom Addons for Elementor (Free)

A performance-first Elementor addons plugin. The focus is the **admin dashboard
and widget settings management**, built on a component architecture that other
plugins extend without this one knowing anything about them.

This is the **free** plugin. The Pro widgets live in a separate plugin,
`xoom-addons-pro`, which requires this one.

## Why two plugins

* The free plugin ships only free code, so it is clean for the WordPress.org
  directory (which rejects plugins that ship locked or trialware features).
* Pro can be versioned, licensed and distributed independently.
* Neither plugin needs the other's internals to change.

## Architecture

```
Bootstrap      xoom-addons-for-elementor.php   constants, autoloader, lifecycle
    │
Services       Plugin .............. container, boot order, Elementor gating
               Settings ............ typed option repository (single source of truth)
               Catalog ............ read model: filtering, counts, edition label
               Assets ............. conditional frontend asset loading
    │
Registries     Widget_Registry ..... declarative widget catalog (filterable)
               Module_Registry ..... declarative extension catalog (filterable)
               Category_Registry ... dashboard categories (filterable)
    │
Managers       Widget_Manager ..... registers only *enabled* widgets with Elementor
               Module_Manager ..... loads only *enabled* extensions
    │
Admin          Admin .............. menu, screens, scoped asset loading
               Ajax ............... nonce + capability checked mutations
               views/ partials ... presentation only
```

**The rule that keeps it consistent:** the dashboard never hardcodes a widget
list. Categories, filters, counts, breadcrumbs and the Elementor registration
pipeline all read from the registries, so a new component shows up everywhere at
once — whether it comes from this plugin or another one.

### Storage

All state lives in flat options, one per group:

| Group | Option | Shape |
| --- | --- | --- |
| Widgets | `xoom_addons_widget_status` | `id => bool` map |
| Extensions | `xoom_addons_module_status` | `id => bool` map |
| General | `xoom_addons_general` | scalar map |
| Performance | `xoom_addons_performance` | scalar map |
| Advanced | `xoom_addons_advanced` | scalar map |

Component status is stored as a **sparse override map**. Anything the user has
never toggled falls back to the registry default, so newly shipped widgets are
enabled automatically and a single toggle is one small write.

## Public API (for extension plugins)

This is the contract the Pro plugin uses. It is stable and intended for reuse.

**Actions**

| Hook | When | Argument |
| --- | --- | --- |
| `xoom_addons_loaded` | `init`, priority 1 | The `Xoom_Addons\Plugin` container |
| `xoom_addons_dashboard_panels` | While rendering the dashboard | The `Xoom_Addons\Catalog` |

**Filters**

| Hook | Purpose |
| --- | --- |
| `xoom_addons_widgets` | Add or remove widget definitions |
| `xoom_addons_modules` | Add or remove extension definitions |
| `xoom_addons_widget_categories` | Add dashboard categories |
| `xoom_addons_settings_defaults` | Add default settings |

Registering a widget is one array entry:

```php
add_filter( 'xoom_addons_widgets', function ( $widgets ) {
    $widgets['my-widget'] = array(
        'title'           => __( 'My Widget', 'my-plugin' ),
        'description'     => __( 'What it does, in one line.', 'my-plugin' ),
        'categories'      => array( 'basic' ),
        'keywords'        => array( 'example' ),
        'icon'            => 'dashicons-star-filled',
        'package'         => 'pro',
        'class'           => 'My_Plugin\\Widgets\\My_Widget',
        'file'            => '', // omit when the class is autoloadable
        'default_enabled' => false,
    );

    return $widgets;
} );
```

The widget card, filters, counts and Elementor registration all follow from
that entry — no other file needs touching.

## Adding a free widget to this plugin

1. Create `widgets/my-widget/class-widget-my-widget.php`:

```php
namespace Xoom_Addons\Widgets\My_Widget;

use Xoom_Addons\Abstracts\Base_Widget;

class Widget_My_Widget extends Base_Widget {
    public function get_name() { return 'xoom-my-widget'; }
    public function get_title() { return __( 'My Widget', 'xoom-addons-for-elementor' ); }
    public function get_icon() { return 'eicon-star'; }
}
```

`Base_Widget` already wires up the Elementor category and the shared stylesheet.

2. Add one entry to `Widget_Registry::definitions()`.

## Adding a setting

Add one entry to the `$tabs` schema in `includes/admin/views/settings.php` and a
matching key in `Settings::schema()` plus `Settings::defaults()`. The form
markup, persistence and sanitisation are all driven from that schema.

## Performance model

* **Disabled means absent.** A disabled widget is never instantiated and its
  files are never included.
* **Assets are registered, not enqueued.** `Assets` registers the shared handles
  on `elementor/frontend/after_register_styles|scripts`. Each widget advertises
  what it needs through `get_style_depends()` / `get_script_depends()`, so
  Elementor prints those handles only on pages that render the widget.
* **`optimize_assets`** turns that behaviour off (assets load everywhere) if a
  site needs it.
* **Admin assets are scoped.** Dashboard CSS/JS is only enqueued on the plugin's
  own screens, verified against the screen hook.

## Security

* Every admin request passes `check_ajax_referer()` and `current_user_can()`.
* Component ids are validated against the registry, never trusted from the
  request. Unknown components are rejected.
* Options pass through a per-group type schema before they are stored;
  non-scalar values are discarded rather than coerced.
* All output is escaped and rendered server side.
* The Post Duplicator extension enforces both nonce and
  `edit_post` / `create_posts` capabilities.

## Accessibility

* Toggles are `role="switch"` with `aria-checked` kept in sync.
* Filters are real buttons with `aria-pressed`; tabs implement the ARIA tab
  pattern with arrow/Home/End keyboard navigation.
* Result counts and save feedback are announced through an `aria-live` region.
* Focus states are visible, and `prefers-reduced-motion` is respected.

## File map

```
xoom-addons-for-elementor.php   Bootstrap and constants
uninstall.php                   Opt-in data removal
includes/
  class-autoloader.php          Namespace → file resolution
  class-plugin.php              Service container and boot
  class-settings.php            Option repository
  class-catalog.php             Filtering, counts, edition label
  class-assets.php              Conditional asset loading
  abstracts/                    Base_Widget, Base_Module
  registries/                   Widget, module and category catalogs
  managers/                     Elementor registration pipelines
  admin/                        Menu, screens, AJAX, views, partials
widgets/                        Shipped free widgets
modules/                        Shipped free extensions
assets/
  admin/css|js/                 Dashboard UI (scoped to .xoom-shell)
  css|js/                       Frontend widget assets
```
