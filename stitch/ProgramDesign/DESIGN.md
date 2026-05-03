---
name: Academic Precision
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#444651'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#747782'
  outline-variant: '#c4c6d2'
  surface-tint: '#495d8e'
  primary: '#001a47'
  on-primary: '#ffffff'
  primary-container: '#1a305d'
  on-primary-container: '#8498cd'
  inverse-primary: '#b2c6fd'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fddd7c'
  on-secondary-container: '#776005'
  tertiary: '#001f2e'
  on-tertiary: '#ffffff'
  tertiary-container: '#00354c'
  on-tertiary-container: '#759eb9'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d9e2ff'
  primary-fixed-dim: '#b2c6fd'
  on-primary-fixed: '#001946'
  on-primary-fixed-variant: '#314575'
  secondary-fixed: '#ffe085'
  secondary-fixed-dim: '#e3c466'
  on-secondary-fixed: '#231b00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#c6e7ff'
  tertiary-fixed-dim: '#a3cce8'
  on-tertiary-fixed: '#001e2d'
  on-tertiary-fixed-variant: '#204b63'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  grid-gutter: 32px
  container-max: 1280px
  stack-gap: 24px
  safe-margin: 40px
  section-gap: 120px
---

## Brand & Style
The brand identity is built on a foundation of intellectual rigor and academic prestige. It utilizes a **Corporate Modern** aesthetic that balances traditional institutional values with forward-thinking technical innovation. The visual language is structured and professional, designed to evoke trust, clarity, and analytical excellence. It targets a high-achieving audience of students, researchers, and industry partners who value precision and systematic thinking. The overall feel is sophisticated and intentional, using deep corporate blues and crisp layouts to establish authority in the field of applied mathematics.

## Colors
The palette is dominated by "Deep Academic Blue," which serves as the primary anchor for headers, buttons, and hero backgrounds. This is complemented by a "Pragmatic Gold" (Secondary) used sparingly for high-priority calls to action and emphasis markers, signifying value and achievement. 

The neutral palette utilizes a cool-toned gray scale starting from a nearly-white surface (`#F7F9FB`) to provide a clean, high-contrast reading environment. Semantic colors are integrated via containers—specifically the use of gold for "achievement" badges and soft blue for "institutional" identifiers.

## Typography
The system relies exclusively on **Inter**, utilizing its systematic and utilitarian nature to convey technical clarity. The type hierarchy is highly structured: 
- **Headlines** use tight line heights and heavy weights (700) to command attention. 
- **Body text** prioritizes readability with a generous 1.6 line-height ratio. 
- **Labels** utilize uppercase tracking and medium-to-bold weights to distinguish navigational or metadata elements from narrative content. 
- Bilingual support is handled by pairing the English bold weights with a Medium weight for Thai script to ensure visual parity.

## Layout & Spacing
The layout follows a **Fixed Grid** model with a maximum container width of 1280px. A generous `section-gap` of 120px is used to define clear boundaries between different thematic areas of the page, promoting focus and reducing cognitive load. 

Internal spacing follows a strict rhythm where cards and interactive blocks use a `stack-gap` of 24px. The grid system is a 12-column structure with 32px gutters, allowing for common 1/2, 1/3, and 5/7 split configurations that accommodate both imagery and dense text effectively.

## Elevation & Depth
The system uses **Tonal Layers** and **Low-contrast outlines** rather than aggressive shadows to define depth.
- **Level 0 (Surface):** The base background layer (`#F7F9FB`).
- **Level 1 (Cards):** Elevated via a white background (`#FFFFFF`) and a very soft `shadow-sm`, often reinforced by a subtle 1px border in `outline-variant`.
- **Level 2 (Active Elements):** Primary buttons use high-contrast solid fills to appear physically "higher" than the rest of the interface.
- **Glassmorphism:** Applied specifically to the navigation bar using a `backdrop-blur-sm` and 95% opacity to maintain context of the underlying content while scrolling.

## Shapes
The shape language is **Rounded**, signaling a modern and approachable institution. 
- **Standard UI elements** (cards, images) use a 0.5rem (8px) radius.
- **Interactive containers** (search bars, pill badges) utilize a `full` (pill) roundedness to distinguish them as tappable or status-indicating elements.
- **Structural accents** like the "AUN-QA" certification box use a 4px left-border accent to reinforce a structured, list-oriented layout.

## Components
- **Buttons:** Primary buttons are pill-shaped with bold labels. Secondary buttons use a squared-off 4px radius with "Pragmatic Gold" fills for high-visibility actions like "Apply Now."
- **Badges:** Small, pill-shaped containers with a 1px border and low-opacity background for category labels (e.g., "Department of Mathematics").
- **Cards:** White backgrounds with a subtle border and 4px colored top-accents to indicate different tracks or program types.
- **List Items:** Feature small icon prefixes (Material Symbols) in neutral tones to guide the eye without competing with the primary text.
- **Navigation:** A fixed, blurred glass bar with active states indicated by a persistent bottom border in the primary brand color.
- **Hero Image:** High-quality photography with a dark navy overlay (`primary` at 80% opacity) to ensure white text legibility.