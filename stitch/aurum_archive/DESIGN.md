# Design System Strategy: The Digital Archivist

## 1. Overview & Creative North Star
The North Star for this design system is **"The Digital Archivist."** In a world of cluttered document management, this system rejects the industrial, grid-heavy "file explorer" aesthetic. Instead, it adopts the language of a high-end editorial boutique—think of a bespoke concierge service where every document is treated as a premium asset. 

We break the "template" look by prioritizing **intentional white space** and **asymmetric focal points**. Large, authoritative typography (Manrope) is paired with the utilitarian precision of Inter. By utilizing a "layered paper" philosophy, we create an interface that feels physically organized, light, and hyper-efficient, moving away from "software" and toward an "experience."

---

## 2. Colors & Surface Philosophy
The palette is rooted in a pristine, gallery-white foundation, punctuated by "Aurum" accents (Gold/Yellow).

### The "No-Line" Rule
To achieve a premium, seamless feel, **1px solid borders are strictly prohibited for sectioning.** Boundaries must be defined solely through background color shifts or tonal transitions.
- **Example:** A sidebar using `surface-container-low` should sit directly against a `surface` main content area. The eye perceives the transition through the value shift, not a harsh line.

### Surface Hierarchy & Nesting
Treat the UI as a series of stacked, fine-paper sheets. Use the surface tiers to define importance:
- **`surface-container-lowest` (#ffffff):** Reserved for the primary content focus (e.g., the document currently being edited or viewed).
- **`surface` (#f8f9fa):** The global canvas.
- **`surface-container-low` (#f3f4f5):** Secondary navigation or utility panels.
- **`surface-container-highest` (#e1e3e4):** Tertiary "drawer" elements or inactive background states.

### The "Glass & Gold" Rule
For floating modals or popovers, use **Glassmorphism**. Combine `surface-container-lowest` at 80% opacity with a `backdrop-blur` of 12px. To provide "soul," use a subtle linear gradient on primary CTAs transitioning from `primary` (#735c00) to `primary_container` (#d4af37). This prevents the gold from looking "flat" or "musty."

---

## 3. Typography
The system employs a dual-font strategy to balance editorial elegance with functional clarity.

*   **Display & Headlines (Manrope):** Used for "Wayfinding." These should feel authoritative. Use `headline-lg` for dashboard summaries to create a high-contrast, magazine-style header.
*   **Body & Labels (Inter):** Used for "Data." Inter’s high x-height ensures readability in dense document lists. 
*   **Signature Styling:** Use `label-sm` in all-caps with 0.05rem letter-spacing for category tags to evoke the feel of a luxury brand's archival label.

---

## 4. Elevation & Depth
Depth is achieved through **Tonal Layering** and environmental lighting, never through heavy drop shadows.

*   **The Layering Principle:** Instead of a shadow, place a `surface-container-lowest` card on a `surface-container-low` section. The subtle contrast creates a natural, soft lift.
*   **Ambient Shadows:** For floating elements (e.g., a "Create New Document" FAB), use a highly diffused shadow: `box-shadow: 0 10px 30px rgba(115, 92, 0, 0.06);`. Note the shadow is tinted with the `primary` color to simulate natural light reflecting off gold accents.
*   **The Ghost Border Fallback:** If a container requires a border for accessibility, use `outline_variant` at **15% opacity**. It should be a whisper of a line, felt rather than seen.

---

## 5. Components

### Buttons
*   **Primary:** A gradient of `primary` to `primary_container`. Text in `on_primary` (#ffffff). Shape: `xl` (0.75rem) for a modern, approachable feel.
*   **Tertiary (Text-only):** Use `primary` color with a 0.5px underline that appears only on hover.

### Input Fields
*   **Styling:** Forgo the box. Use a `surface-container-low` background with a `primary` 2px bottom-indicator on focus. 
*   **Labels:** Use `label-md` floating above the field in `on_surface_variant`.

### Cards & Lists (The DMS Core)
*   **No Dividers:** Forbid the use of horizontal rules (`<hr>`). Separate list items using `spacing-4` (1rem) of vertical white space or a hover state that shifts the background to `surface-container-high`.
*   **File Preview Chips:** Use `secondary_container` with `secondary` text. The roundedness should be `full` to contrast against the `xl` roundedness of cards.

### Additional Signature Components
*   **Status Breadcrumbs:** Instead of a standard path, use a "Stepped Indicator" in `primary_fixed_dim` to show document progress (e.g., Draft > Review > Archived).
*   **The "Aurum" Indicator:** A thin vertical gold bar (`primary`) used to highlight the "Active" document in a list, replacing a full-row highlight.

---

## 6. Do's and Don'ts

### Do
*   **Do** use `spacing-12` (3rem) and `spacing-16` (4rem) to let major sections breathe.
*   **Do** use `primary` gold for critical actions, but use `tertiary` (#415ba4) for functional, non-brand indicators like "System Update" or "Download Complete" to avoid gold fatigue.
*   **Do** ensure all text on gold backgrounds meets AA accessibility standards by using `on_primary_container` (#554300) for high-contrast legibility.

### Don't
*   **Don't** use black (#000000) for text. Use `on_surface` (#191c1d) to maintain the sophisticated, softened contrast of the palette.
*   **Don't** use standard `md` (0.375rem) corners for everything. Mix `xl` (0.75rem) for large containers and `full` for interactive chips to create visual rhythm.
*   **Don't** ever use a 100% opaque border. It breaks the "Digital Archivist" illusion of seamless, layered paper.