# Shipping Option Conditions for WooCommerce — Reference

## Plugin Header

| Field            | Value                                                        |
|------------------|--------------------------------------------------------------|
| Plugin Name      | Shipping Option Conditions for WooCommerce                   |
| Plugin URI       | https://github.com/weboptics/shipping-option-conditions-wc   |
| Version          | 1.2.1                                                        |
| Requires WP      | 5.8                                                          |
| Requires PHP     | 7.2                                                          |
| Author           | WebOptics (https://weboptics.co/)                            |
| License          | GPL v2 or later                                              |
| Text Domain      | shipping-option-conditions-wc                                |
| Requires Plugin  | woocommerce                                                  |

---

## File Tree

```
shipping-option-conditions-wc/
├── includes/
│   ├── class-hs-wcsh-init.php          # Core plugin class
│   ├── class-hs-wcsh-cs.php            # Custom WC_Shipping_Method
│   └── tables/
│       └── class-hs-wcsh-state-table.php  # WP_List_Table for admin UI
├── .github/workflows/master.yml        # GitHub Actions CI
├── .vscode/                            # Editor snippets & settings
├── .wordpress-org/                     # Plugin banner/icon/screenshots
├── changelog.txt
├── composer.json / composer.lock
├── package.json / package-lock.json
├── phpcs.xml.dist
├── README.md
└── shipping-option-conditions-wc.php   # Main plugin entry point
```

---

## Constants (defined in main file)

| Constant              | Value                          |
|-----------------------|--------------------------------|
| `HSWCSH_VERSION`      | `'1.2.1'`                      |
| `HSWCSH_PLUGIN_DIR`   | Absolute path to plugin folder |
| `HSWCSH_PLUGIN_URL`   | URL to plugin folder           |

---

## Classes

### `HS_WCSH_Init` — [includes/class-hs-wcsh-init.php](includes/class-hs-wcsh-init.php)

Core class. Instantiated via `hs_wcsh_run()` on `plugins_loaded`.

| Method | Description |
|--------|-------------|
| `__construct()` | Registers all hooks. Bails early if WooCommerce is not active. |
| `add_shipping_section($sections)` | Appends "Conditional Shipping" entry to WooCommerce → Settings → Shipping sections. |
| `add_shipping_section_settings($settings, $current_section)` | Renders zone-tab navigation + state table for the Conditional Shipping admin page. |
| `hide_shipping_when_free_is_available($rates)` | Filters `woocommerce_package_rates` (priority 100): removes non-free methods when a free-shipping method with `woo_show_hide` enabled is present. Respects `woo_show_hide_override` on individual methods. |
| `shipping_instance_form_add_extra_fields_free($settings)` | Adds "Show / Hide" checkbox to Free Shipping method settings. |
| `shipping_instance_form_add_extra_fields_others($settings)` | Adds "Show / Hide - Override" checkbox to all other shipping method settings. |
| `shipping_instance_form_fields_filters()` | On `woocommerce_init`: attaches form-field filters to every registered shipping method. |
| `custom_plugin_activation_notice()` | `admin_notices`: shows a warning if WooCommerce is missing. |
| `custom_admin_footer()` | `admin_footer`: injects inline CSS (free-shipping checkbox style) and jQuery that triggers checkout update on billing/shipping city change. |
| `custom_wp_footer()` | `wp_footer`: injects empty `<style>` block (placeholder). |
| `save_plugin_woocommerce_tab_data()` | Iterates posted `zone_data` and calls `update_option()` for each state cost. |
| `handle_save_for_custom_shipping_tab()` | Handles form POST on `woocommerce_settings_save_shipping` for the conditional section. |
| `recursive_sanitize_text_field($array)` | Recursively sanitizes nested arrays before saving. |

---

### `HS_WCSH_CS` — [includes/class-hs-wcsh-cs.php](includes/class-hs-wcsh-cs.php)

Custom shipping method. Extends `WC_Shipping_Method`.

| Property / Method | Value / Description |
|-------------------|---------------------|
| `$id` | `'conditional_shipping_class'` |
| Supports | `'shipping-zones'`, `'instance-settings'` |
| `__construct($instance_id)` | Sets up method ID, title, supports array. |
| `init()` | Calls `init_form_fields()` and `init_settings()`, registers save action. |
| `init_form_fields()` | Defines two fields: `title` (text) and `condition` (select — currently only "By State/County"). |
| `calculate_shipping($package)` | Gets shipping zone for package → extracts destination state → reads cost from `conditional_shipping_option_[zone_id]_[state]` option → adds rate. |

---

### `HS_WCSH_State_Table` — [includes/tables/class-hs-wcsh-state-table.php](includes/tables/class-hs-wcsh-state-table.php)

Admin list table. Extends `WP_List_Table`.

| Method | Description |
|--------|-------------|
| `__construct($zone_id)` | Stores zone ID, sets up parent table args. |
| `prepare_items()` | Paginates at 10 items/page. Calls `get_shipping_zones()` to populate `$this->items`. |
| `get_shipping_zones()` | Reads zone locations → maps state codes to names via WooCommerce → fetches saved costs from DB → returns rows with HTML `<input>` for amount. |
| `get_columns()` | Returns three columns: Zone Name (state code), Zone Code (state name + country), Zone Amount (cost input). |
| `column_default($item, $column)` | Echoes cell value. |
| `get_states_by_country_codes($codes)` | Merges WooCommerce state lists for given country codes, prefixed with country code. |

---

## Database / Options

No custom DB tables. All data is stored as WordPress options.

| Option Key Pattern | Content |
|--------------------|---------|
| `conditional_shipping_option_{ZONE_ID}_{STATE_CODE}` | Shipping cost (string, e.g. `"10.99"`). Empty = free. |
| `woocommerce_{METHOD_ID}_{INSTANCE_ID}_settings` | WC method settings array including `woo_show_hide` and `woo_show_hide_override` booleans. |

---

## Hooks

### Actions

| Hook | Priority | Callback | Purpose |
|------|----------|----------|---------|
| `plugins_loaded` | default | `hs_wcsh_run()` | Bootstrap plugin |
| `woocommerce_shipping_init` | default | loads `HS_WCSH_CS` | Register custom method class |
| `woocommerce_init` | default | `shipping_instance_form_fields_filters()` | Attach per-method form-field filters |
| `woocommerce_settings_save_shipping` | default | `handle_save_for_custom_shipping_tab()` | Save state costs on settings POST |
| `woocommerce_update_options_shipping_conditional_shipping_class` | default | `init_settings()` | Save method-level options |
| `admin_notices` | default | `custom_plugin_activation_notice()` | WooCommerce missing warning |
| `admin_footer` | default | `custom_admin_footer()` | Inline CSS + JS in admin |
| `wp_footer` | default | `custom_wp_footer()` | Placeholder style on frontend |

### Filters

| Hook | Priority | Callback | Purpose |
|------|----------|----------|---------|
| `woocommerce_get_sections_shipping` | default | `add_shipping_section()` | Add "Conditional Shipping" tab |
| `woocommerce_get_settings_shipping` | default | `add_shipping_section_settings()` | Render zone/state table |
| `woocommerce_package_rates` | 100 | `hide_shipping_when_free_is_available()` | Hide non-free rates |
| `woocommerce_shipping_methods` | default | adds `HS_WCSH_CS` | Register custom shipping method |
| `woocommerce_shipping_instance_form_fields_free_shipping` | default | `shipping_instance_form_add_extra_fields_free()` | Add Show/Hide field to Free Shipping |
| `woocommerce_shipping_instance_form_fields_{METHOD_ID}` | default | `shipping_instance_form_add_extra_fields_others()` | Add Override field to other methods |

---

## Admin UI

**Location:** WooCommerce → Settings → Shipping → Conditional Shipping

- **Zone tabs:** one tab per WooCommerce shipping zone, identified by `custom_tab=zone-{ZONE_ID}` URL param.
- **State table:** 10 rows per page; columns — state code, state name + country, cost input.
- **Form fields:** `zone_data[{ZONE_ID}][{STATE_CODE}]` posted on save.
- **Shipping method modals:**
  - Free Shipping: extra checkbox `woo_show_hide` ("Show / Hide").
  - All other methods: extra checkbox `woo_show_hide_override` ("Show / Hide - Override").

---

## No REST API

No REST endpoints are registered. All data flows through standard WP admin forms.

---

## Key Interactions / Logic Flow

1. **Admin saves state costs** → `handle_save_for_custom_shipping_tab()` → `save_plugin_woocommerce_tab_data()` → `update_option('conditional_shipping_option_{zone}_{state}', cost)`.
2. **Customer at checkout** → `woocommerce_package_rates` filter fires → `hide_shipping_when_free_is_available()` inspects each rate's `woo_show_hide` / `woo_show_hide_override` settings → removes ineligible rates.
3. **`HS_WCSH_CS::calculate_shipping()`** → determines destination zone → looks up cost option → adds shipping rate with that cost.
4. **City field change (frontend/checkout)** → jQuery triggers WC checkout update so rates recalculate dynamically.
