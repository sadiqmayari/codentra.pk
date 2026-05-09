<?php /* Critical above-the-fold CSS — keep under ~5KB. Hand-tuned from style.css. */ ?>
<style id="critical-css">
:root{--clr-bg:#0A1C28;--clr-bg-2:#0F2533;--clr-accent:#2A9D8F;--clr-accent-2:#36b8a8;--clr-text:#fff;--clr-cta:#F4A261;--clr-cta-2:#E76F51;--clr-surface:rgba(255,255,255,.04);--clr-border:rgba(255,255,255,.10);--clr-muted:rgba(255,255,255,.65);--grad-cta:linear-gradient(135deg,#F4A261 0%,#E76F51 100%);--grad-glow:radial-gradient(ellipse at center,rgba(42,157,143,.25) 0%,transparent 70%);--radius-sm:.5rem;--radius-md:.75rem;--header-h:72px;--container-max:1200px;--container-pad:1.25rem;--fs-xs:.875rem;--fs-sm:1rem;--fs-md:1.25rem}
@font-face{font-family:'Ubuntu';font-style:normal;font-weight:400;font-display:swap;src:url('/public/fonts/ubuntu-400.woff2') format('woff2')}
@font-face{font-family:'Ubuntu';font-style:normal;font-weight:700;font-display:swap;src:url('/public/fonts/ubuntu-700.woff2') format('woff2')}
*,*::before,*::after{box-sizing:border-box}*{margin:0;padding:0}
html{-webkit-text-size-adjust:100%}
body{font-family:'Ubuntu',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;font-weight:400;line-height:1.65;color:var(--clr-text);background:var(--clr-bg);min-height:100vh;-webkit-font-smoothing:antialiased;overflow-x:hidden}
img,svg{display:block;max-width:100%;height:auto}
button{background:none;border:none;cursor:pointer;font:inherit;color:inherit}
a{color:var(--clr-accent);text-decoration:none;transition:color .18s ease}
ul{list-style:none}
h1,h2,h3{font-weight:700;line-height:1.15;letter-spacing:-.02em}
:focus-visible{outline:2px solid var(--clr-accent);outline-offset:3px;border-radius:4px}
.skip-link{position:absolute;top:-40px;left:1rem;background:var(--clr-accent);color:#fff;padding:.5rem 1rem;border-radius:var(--radius-sm);z-index:1000;font-weight:500}
.skip-link:focus{top:1rem}
.container{width:100%;max-width:var(--container-max);margin-inline:auto;padding-inline:var(--container-pad)}
.site-header{position:fixed;top:0;left:0;right:0;z-index:100;height:var(--header-h);display:flex;align-items:center;background:transparent;border-bottom:1px solid transparent;transition:background .28s,backdrop-filter .28s,border-color .28s}
.site-header.is-scrolled{background:rgba(10,28,40,.78);-webkit-backdrop-filter:blur(14px) saturate(140%);backdrop-filter:blur(14px) saturate(140%);border-bottom-color:var(--clr-border)}
.site-header__inner{display:flex;align-items:center;justify-content:space-between;width:100%}
.brand{font-size:1.4rem;font-weight:700;letter-spacing:-.03em;color:var(--clr-text);display:inline-flex;align-items:baseline}
.brand__mark{color:var(--clr-accent);font-size:1.6rem;margin-right:1px}
.primary-nav__list{display:none;align-items:center;gap:1.75rem}
.primary-nav__link{font-size:var(--fs-sm);font-weight:500;color:var(--clr-text);position:relative;padding:.25rem 0}
.nav-toggle{display:inline-flex;flex-direction:column;justify-content:center;align-items:center;width:44px;height:44px;gap:5px;border-radius:var(--radius-sm)}
.nav-toggle__bar{display:block;width:24px;height:2px;background:var(--clr-text);transition:transform .28s,opacity .28s}
@media(min-width:768px){.nav-toggle{display:none}.primary-nav__list{display:flex}}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.875rem 1.75rem;border-radius:var(--radius-md);font-size:var(--fs-sm);font-weight:500;text-decoration:none;white-space:nowrap;cursor:pointer;transition:transform .18s ease,box-shadow .28s,background .28s}
.btn--cta{background:var(--grad-cta);color:#fff;box-shadow:0 4px 18px rgba(244,162,97,.30)}
.btn--ghost{background:transparent;color:var(--clr-text);border:1px solid var(--clr-border)}
.hero{position:relative;min-height:100vh;min-height:100svh;padding-top:var(--header-h);display:flex;align-items:center;overflow:hidden;isolation:isolate;background:radial-gradient(ellipse at 50% 40%,rgba(42,157,143,.18) 0%,transparent 60%),var(--clr-bg)}
.hero::before{content:'';position:absolute;inset:0;background:var(--grad-glow);pointer-events:none;z-index:-1}
.hero__canvas{position:absolute;inset:0;width:100%;height:100%;z-index:-1;opacity:0;transition:opacity .6s ease}
.hero__canvas.is-ready{opacity:1}
.hero__overlay{position:absolute;inset:0;background:linear-gradient(180deg,transparent 60%,var(--clr-bg) 100%);z-index:-1;pointer-events:none}
.hero__inner{text-align:center;position:relative;z-index:1;padding-block:4rem}
.hero__eyebrow{font-size:var(--fs-xs);letter-spacing:.25em;text-transform:uppercase;color:var(--clr-accent);font-weight:500;margin-bottom:1.25rem}
.hero__title{font-size:clamp(2.25rem,7vw,3.75rem);font-weight:700;line-height:1.05;letter-spacing:-.04em;margin-bottom:1.5rem}
.hero__title .dot{color:var(--clr-accent);display:inline-block;transform:translateY(-.05em)}
.hero__sub{font-size:clamp(1rem,1.5vw,1.25rem);color:var(--clr-muted);font-weight:300;max-width:640px;margin:0 auto 2.5rem}
.hero__cta{display:flex;flex-wrap:wrap;gap:1rem;justify-content:center}
[data-reveal]{opacity:0;transform:translateY(24px);transition:opacity .7s cubic-bezier(.4,0,.2,1),transform .7s cubic-bezier(.4,0,.2,1);will-change:opacity,transform}
[data-reveal].is-visible{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}[data-reveal]{opacity:1!important;transform:none!important}.hero__canvas{opacity:1!important}}
</style>
