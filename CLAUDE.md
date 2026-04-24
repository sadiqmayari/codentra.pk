# Codentra.pk — Engineering & Design System

## Product Vision
Codentra.pk is a modern digital solutions and growth systems website.
It must feel like a premium global tech provider (SaaS-grade polish, not a local agency site).

---

## 🧱 Tech Stack
- Next.js (static export only)
- Tailwind CSS for UI styling
- Fully responsive design system
- Component-based architecture

---

## 🚀 Deployment Environment
Target: Hostinger shared hosting (public_html)

### Strict Rules:
- Must support static export only
- No SSR (Server-Side Rendering)
- No API routes
- No Node.js runtime features
- Final output must be `/out` folder

---

## 🎨 Design Philosophy

### Visual Style:
- Modern SaaS aesthetic (Stripe, Vercel, Linear-inspired)
- Clean spacing with strong typography hierarchy
- Soft gradients + subtle depth (no clutter)
- Glassmorphism only when purposeful
- Minimal but premium UI language

### UI Principles:
- Mobile-first design
- High contrast readability
- Clear conversion paths (CTA-driven)
- Generous whitespace
- Micro-interactions where useful (hover, transitions)

---

## 🧩 Layout System

- Section-based architecture:
  - Hero Section (conversion-focused)
  - Services Section (clear value blocks)
  - Case Studies / Proof
  - Process / Workflow
  - Testimonials
  - Final CTA

- Every page must follow:
  Awareness → Trust → Conversion flow

---

## ⚙️ Development Rules

- Build reusable components only
- Avoid duplication of UI logic
- Keep components small and modular
- Maintain consistent spacing system
- Use semantic HTML for SEO

---

## 🔍 SEO Standards

Every page must include:
- Proper heading hierarchy (H1 → H2 → H3)
- Meta title + description
- Keyword-aligned content structure
- Fast loading performance
- Clean URLs
- Image optimization

---

## ⚡ Performance Rules

- Lightweight pages only
- Avoid unnecessary libraries
- Optimize images before commit
- No blocking scripts
- Keep Core Web Vitals strong

---

## 🔁 Workflow

1. Understand feature requirement
2. Design UI structure first
3. Implement components
4. Ensure static export compatibility
5. Test build (`npm run build`)
6. Confirm `/out` output
7. Commit changes
8. Deploy via Git → Hostinger

---

## 🚫 Hard Restrictions

- No backend logic in frontend code
- No authentication systems
- No database dependencies
- No server actions
- No dynamic runtime APIs

---

## 🎯 Product Thinking Rule

Every section must answer:
- What problem does this solve?
- Why should a client trust us?
- What action should they take next?

If it doesn’t contribute to conversion or clarity → remove it.

---

## Legal Pages Style Rules

- Privacy Policy and Terms must follow a clean SaaS-style design
- Use simple, human-readable language (no legal complexity overload)
- Structure content with clear headings and sections
- Must match Codentra branding (modern, minimal, professional)
- No walls of text — break into digestible sections
- Maintain consistency with overall design system

---

## Brand Philosophy

Codentra operates on one principle:
Code → Automate → Scale

Every feature must align with at least one of:
- Writing efficient code
- Automating a manual process
- Enabling business scalability
