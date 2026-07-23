# Design System & Aesthetic Contract

## Visual World: Patagonian Alpine Editorial (Premium & Immersive)

We reject the standard "corporate municipal" template (bland light blues, boring grid cards, static layouts) and instead build a high-energy, dark-themed, glassmorphic editorial experience. It evokes the mystery, grandeur, and texture of the Patagonian mountains (basalt, snow, forests, and wild berries).

---

## Design Tokens

### Color Palette (HSL & Hex)
We employ a **Committed & Drenched** color strategy to define distinct spaces:
- **Basalt (Base Background)**: `hsl(210, 10%, 8%)` / `#111315` (Deep, warm charcoal, not pure black, to soften reading on screens).
- **Alabaster (Text & High Contrast)**: `hsl(210, 20%, 98%)` / `#f7f8f9` (Crisp, off-white, avoiding harsh absolute white).
- **Glacier Glass (Surfaces & Cards)**: `hsla(210, 10%, 15%, 0.6)` with `backdrop-filter: blur(16px)` and thin border `hsla(0, 0%, 100%, 0.08)`.
- **Wild Berry (Brand Accent - Primary)**: `hsl(340, 65%, 45%)` / `#b02a53` (Vibrant berry magenta, extracted from the Esquel Acelera/Raíz logo typography, used for primary actions, highlight states, and select borders).
- **Lichen Green (Secondary Accent)**: `hsl(150, 40%, 35%)` / `#236f4c` (Muted forest green, used for Raíz elements and badge backgrounds).
- **Slate Grey (Muted Text & Boundaries)**: `hsl(210, 8%, 55%)` / `#828a91` (Neutral grey for secondary information and borders).

### Typography
- **Display & Headings**: `font-family: 'Outfit', sans-serif;` (Clean, geometric sans-serif that feels premium, modern, and open).
- **Body Text**: `font-family: 'Inter', system-ui, sans-serif;` (Highly legible sans-serif for reading dense criteria and completing forms).
- **Monospace (Badges, Timelines, Action Items)**: `font-family: 'Space Mono', monospace;` (For chronological details, dates, and strict technical parameters).

### Layout & Spacing System
- **Scale**: Multiples of 8px (8, 16, 24, 32, 48, 64, 96, 128).
- **Grid**: 12-column responsive grid on desktop with large gutters (32px), collapsing to single-column on mobile with 20px padding.
- **Section Spacing**: Large vertical padding (`padding: 120px 0` on desktop, `80px 0` on mobile) to allow the content to breathe.

### Motion & Transitions
- **Cubic Bezier**: All interactive elements (hover, focus, modals) transition using:
  `transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);`
- **Focus Rings**: A custom dual-ring focus state for accessibility:
  `outline: 2px solid var(--color-wild-berry); outline-offset: 4px;`

---

## Component-Specific Rules

### 1. Landing Viewport (The Thesis)
- **Visuals**: Full-screen layout with a high-resolution dark background of Esquel (mountains/lakes) overlayed with a subtle dark vignette.
- **Header**: Sticky navigation with a frosted-glass background (`backdrop-filter: blur(12px)`), displaying the unified white logo.
- **Hero Title**: Inter-as-display style bold typography, highlighting the core claim ("Hagamos juntos. Dejemos cosas andando.").
- **Call to Action**: High-contrast, glowing button in Wild Berry magenta.

### 2. Multi-step Form (The Application)
- **Flow**: Divided into distinct visual cards (Slide-in animations from right to left).
- **Progress Indicator**: A clean horizontal line with micro-animations indicating steps (General, Specific Program, Commitment).
- **Inputs**: Floating label inputs with deep Glacier Glass background and a Wild Berry outline on active state.

### 3. CRM Dashboard (The Backend)
- **Table Layout**: High density, dark-slate background with borders only on horizontal rows (`1px solid hsla(0, 0%, 100%, 0.05)`).
- **Card View**: Gridded layout of Glacier Glass cards, showing status badges (Pendiente, Preseleccionado, Aprobado) with customized Lichen Green, Slate Grey, and Wild Berry borders.
- **Action Drawers**: Right-side sliding panel for adding evaluation scores and notes, preventing user disruption.
