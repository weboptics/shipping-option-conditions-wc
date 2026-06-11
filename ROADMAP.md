# Roadmap — Shipping Option Conditions for WooCommerce

---

## v1.2.2 — Cart Total Condition *(Free)*

- Show/hide a shipping method when cart total is above or below a set threshold
- Admin UI: min/max amount fields per shipping method instance

---

## v1.2.3 — Product Category Condition *(Free)*

- Show/hide a shipping method when the cart contains items from specific product categories
- Admin UI: multi-select category picker per shipping method instance

---

## v1.2.4 — Shipping Class Condition *(Free)*

- Show/hide a shipping method based on WooCommerce shipping classes present in the cart
- Admin UI: multi-select shipping class picker per shipping method instance

---

## v1.2.5 — Cart Weight Condition *(Free)*

- Show/hide a shipping method when cart weight is above or below a set limit
- Admin UI: min/max weight fields per shipping method instance

---

## v1.3 — UX & Admin Polish *(Free)*

- Replace inline JS/CSS with properly enqueued assets
- Add rule labels so admins can name/describe each condition
- Show active rule count on the Shipping settings screen
- Input validation and user-facing error messages on save

---

## v1.4 — Postcode & Zip Support *(Free)*

- Condition by postcode range or specific zip codes
- More granular than state — a common merchant ask and a clear differentiator from built-in WooCommerce zones

---

## v2.0 — Rule Builder + AND/OR Logic *(Free core + Pro)*

This is the milestone that justifies a WooCommerce Marketplace submission.

**Free:**
- Single-condition rules per shipping method

**Pro:**
- Multiple conditions per rule with AND/OR logic
- Rule priority ordering (drag-and-drop)
- Duplicate/copy rules
- Enable/disable toggle per rule without deleting

---

## v2.1 — Pro-Only Advanced Conditions *(Pro)*

- **Customer role** — different rates for wholesale vs retail
- **Day/time** — disable express shipping on weekends
- **Coupon applied** — unlock free shipping with specific coupons
- **Specific products** — condition on individual SKUs

---

## v2.2 — Import / Export + Reporting *(Pro)*

- Export all rules as JSON (for staging → production migrations)
- Import rules from JSON
- Simple log: which rule triggered on last X orders

---

## Suggested Priority Order

v1.2.2 → v1.2.3 → v1.2.4 → v1.2.5 → v1.3 → v1.4 → v2.0 (free core) → v2.1 → v2.2
