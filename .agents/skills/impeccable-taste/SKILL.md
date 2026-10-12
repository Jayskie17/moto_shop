---
name: impeccable-taste
description: >-
  Impeccable Taste – A design and code quality skill that enforces premium,
  modern, and aesthetically refined standards for UI, UX, and code output.
  Activates best-practice design principles: rich color palettes, micro-animations,
  glassmorphism, smooth gradients, responsive layouts, and clean semantic HTML/CSS/PHP.
---

# Impeccable Taste Skill

This skill enforces **premium design and code quality** across all outputs.

---

## ?? Design Principles

### Color & Typography
- Use curated HSL-based color palettes — never plain red/blue/green.
- Dark mode first: deep backgrounds (#0f0f1a, #1a1a2e) with vibrant accent colors.
- Use Google Fonts: prefer `Inter`, `Outfit`, or `Plus Jakarta Sans`.
- Font sizes follow a modular scale (base 16px, ratio 1.25).

### Layout & Spacing
- Use CSS Grid and Flexbox for all layouts.
- Consistent spacing: multiples of 4px (4, 8, 12, 16, 24, 32, 48, 64).
- Cards use subtle box-shadows: `0 8px 32px rgba(0,0,0,0.2)`.
- Border radius: 12px for cards, 8px for inputs, 6px for buttons.

### Visual Effects
- **Glassmorphism**: `backdrop-filter: blur(16px)` with semi-transparent backgrounds.
- **Gradients**: smooth linear or radial gradients for hero sections and CTAs.
- **Micro-animations**: hover transitions `0.2s ease`, entrance animations with `@keyframes`.
- **Shadows**: layered box-shadows for depth.

---

## ?? Code Quality Principles

### PHP
- Always use `htmlspecialchars()` for output escaping.
- CSRF tokens on every form.
- PDO with prepared statements — never raw SQL interpolation.
- Separate concerns: logic in backend, presentation in frontend.

### HTML
- Semantic elements: `<main>`, `<section>`, `<article>`, `<nav>`, `<header>`, `<footer>`.
- All inputs have associated `<label>` elements.
- ARIA attributes on interactive elements.
- Single `<h1>` per page, proper heading hierarchy.

### CSS
- BEM naming convention for classes.
- CSS custom properties (variables) for all colors, spacing, and fonts.
- Mobile-first responsive design with breakpoints at 480px, 768px, 1024px, 1280px.
- No inline styles — all styles in external CSS files.

---

## ? Checklist Before Delivering Output

- [ ] Color palette is curated and harmonious (not default browser colors)
- [ ] Google Font is imported and applied
- [ ] Responsive on mobile, tablet, and desktop
- [ ] Hover/focus states on all interactive elements
- [ ] Smooth transitions and micro-animations present
- [ ] Semantic HTML used throughout
- [ ] PHP output is properly escaped
- [ ] No inline styles
- [ ] Accessibility: labels, ARIA, keyboard-navigable
- [ ] Premium "wow factor" — would impress a designer on first glance

---

## ?? Example CSS Variable System

```css
:root {
  /* Colors */
  --color-bg:        #0f0f1a;
  --color-surface:   rgba(255,255,255,0.05);
  --color-primary:   hsl(250, 80%, 65%);
  --color-accent:    hsl(190, 90%, 55%);
  --color-text:      #e8e8f0;
  --color-muted:     #8888aa;
  --color-danger:    hsl(0, 75%, 60%);
  --color-success:   hsl(145, 65%, 50%);

  /* Spacing */
  --space-xs: 4px;
  --space-sm: 8px;
  --space-md: 16px;
  --space-lg: 24px;
  --space-xl: 48px;

  /* Typography */
  --font-body:    "Inter", sans-serif;
  --font-heading: "Outfit", sans-serif;
  --text-sm:  0.875rem;
  --text-md:  1rem;
  --text-lg:  1.25rem;
  --text-xl:  1.563rem;
  --text-2xl: 1.953rem;

  /* Effects */
  --radius-sm: 6px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --shadow-md: 0 8px 32px rgba(0,0,0,0.25);
  --shadow-lg: 0 16px 48px rgba(0,0,0,0.4);
  --glass-bg:  rgba(255,255,255,0.05);
  --glass-border: 1px solid rgba(255,255,255,0.1);
  --blur: blur(16px);
  --transition: 0.2s ease;
}
```
