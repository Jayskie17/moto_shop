---
name: taste
description: >-
  Taste – A creative UI/UX direction skill that generates multiple distinct
  design variations (themes, palettes, moods) for any interface. Use it to
  explore Dark, Glassmorphism, Pastel, Cyberpunk, Minimal, and other visual
  directions before committing to a final design. Pairs perfectly with the
  impeccable-taste skill for final polish.
---

# Taste Skill

The **Taste** skill helps you explore and choose a design direction by generating
multiple curated visual themes for any UI component or page. Think of it as a
**design mood board turned into code**.

---

## ?? How to Use This Skill

When asked to apply "taste", always:
1. **Propose 3 distinct design themes** (unless the user specifies a number).
2. For each theme, output: **name, palette, fonts, effects, and a code sample**.
3. Ask the user which theme they prefer before proceeding to full implementation.

---

## ?? Available Design Themes

### 1. ?? Dark Elegance
- **Mood**: Professional, sleek, high-contrast
- **Palette**: Deep navy `#0a0e1a`, electric violet `hsl(258, 80%, 65%)`, soft white `#e8e8f4`
- **Font**: `Plus Jakarta Sans`
- **Effects**: Subtle glow on focus, card depth via layered shadows
- **Best for**: Dashboards, admin panels, SaaS tools

```css
:root {
  --bg: #0a0e1a;
  --surface: #12172b;
  --primary: hsl(258, 80%, 65%);
  --text: #e8e8f4;
  --muted: #6b7280;
  --border: rgba(255,255,255,0.08);
  --shadow: 0 8px 32px rgba(0,0,0,0.4);
}
```

---

### 2. ?? Glassmorphism
- **Mood**: Modern, airy, futuristic
- **Palette**: Dark base `#0f0f1a`, frosted glass panels `rgba(255,255,255,0.06)`, cyan accent `hsl(190,90%,55%)`
- **Font**: `Inter`
- **Effects**: `backdrop-filter: blur(20px)`, transparent borders, glowing accents
- **Best for**: Login pages, landing pages, creative portfolios

```css
:root {
  --bg: linear-gradient(135deg, #0f0f1a 0%, #1a1040 100%);
  --surface: rgba(255,255,255,0.06);
  --surface-border: 1px solid rgba(255,255,255,0.12);
  --blur: blur(20px);
  --primary: hsl(190, 90%, 55%);
  --text: #f0f0ff;
  --shadow: 0 8px 32px rgba(0,0,0,0.3);
}
```

---

### 3. ?? Soft Pastel
- **Mood**: Friendly, warm, approachable
- **Palette**: Cream `#faf8f5`, dusty rose `hsl(340,60%,70%)`, sage green `hsl(150,35%,60%)`
- **Font**: `Outfit`
- **Effects**: Soft shadows, rounded corners (20px), warm hover states
- **Best for**: E-commerce, health apps, consumer-facing UIs

```css
:root {
  --bg: #faf8f5;
  --surface: #ffffff;
  --primary: hsl(340, 60%, 70%);
  --accent: hsl(150, 35%, 60%);
  --text: #2d2d2d;
  --muted: #888;
  --shadow: 0 4px 20px rgba(0,0,0,0.08);
  --radius: 20px;
}
```

---

### 4. ? Cyberpunk Neon
- **Mood**: Bold, energetic, futuristic
- **Palette**: Pitch black `#050505`, neon yellow `hsl(55,100%,50%)`, hot pink `hsl(315,100%,55%)`
- **Font**: `Space Grotesk`
- **Effects**: Neon text-shadow, glitch hover, sharp corners (2px radius)
- **Best for**: Gaming, tech startups, event pages

```css
:root {
  --bg: #050505;
  --surface: #0d0d0d;
  --primary: hsl(55, 100%, 50%);
  --accent: hsl(315, 100%, 55%);
  --text: #f5f5f5;
  --glow: 0 0 20px hsl(55,100%,50%), 0 0 60px hsl(55,100%,30%);
  --radius: 2px;
}
```

---

### 5. ?? Ultra Minimal
- **Mood**: Clean, focused, distraction-free
- **Palette**: Pure white `#ffffff`, near-black `#111111`, single accent `hsl(220,90%,55%)`
- **Font**: `Inter` (light weight 300)
- **Effects**: No shadows, 1px borders, generous whitespace
- **Best for**: Blogs, documentation, professional portfolios

```css
:root {
  --bg: #ffffff;
  --surface: #fafafa;
  --primary: hsl(220, 90%, 55%);
  --text: #111111;
  --muted: #999;
  --border: 1px solid #e5e5e5;
  --radius: 4px;
}
```

---

### 6. ?? Warm Sunset
- **Mood**: Inviting, energetic, premium
- **Palette**: Deep charcoal `#1a1410`, amber `hsl(35,95%,55%)`, terracotta `hsl(15,80%,55%)`
- **Font**: `Outfit`
- **Effects**: Warm gradient backgrounds, orange glows on hover
- **Best for**: Restaurants, motor shops, lifestyle brands

```css
:root {
  --bg: #1a1410;
  --surface: #231c16;
  --primary: hsl(35, 95%, 55%);
  --accent: hsl(15, 80%, 55%);
  --text: #f5ede0;
  --gradient: linear-gradient(135deg, hsl(35,95%,55%), hsl(15,80%,55%));
  --shadow: 0 8px 32px rgba(255,140,0,0.15);
}
```

---

## ?? Workflow When Applying This Skill

```
1. User requests a design for a component/page
2. Taste skill proposes 3 themes (from the list above, or custom)
3. For each theme: show name, palette swatches, font, and a mini code snippet
4. User picks a theme
5. Full implementation is done using that theme's variables
6. Apply impeccable-taste skill for final quality check
```

---

## ? Output Format per Theme Proposal

Always present themes in this format:

```
?? Theme Name
Mood: [one sentence]
Colors: background | surface | primary | text
Font: [Google Font name]
Effects: [comma-separated list]
Best for: [use cases]
[CSS snippet]
```
