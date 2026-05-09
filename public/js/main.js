/**
 * Codentra — main.js
 * - Header scroll state
 * - Mobile menu toggle (with focus trap basics + escape close)
 * - IntersectionObserver scroll reveal
 * - Smooth scroll for in-page anchors
 */

(function () {
  'use strict';

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── Header scroll state ────────────────────────────────────────────────
  const header = document.querySelector('[data-header]');
  if (header) {
    let ticking = false;
    const update = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 24);
      ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(update);
        ticking = true;
      }
    }, { passive: true });
    update();
  }

  // ── Mobile menu ────────────────────────────────────────────────────────
  const navToggle = document.querySelector('[data-nav-toggle]');
  const navMenu   = document.querySelector('[data-nav-menu]');

  if (navToggle && navMenu) {
    const closeMenu = () => {
      navToggle.setAttribute('aria-expanded', 'false');
      navMenu.classList.remove('is-open');
      document.body.style.overflow = '';
    };
    const openMenu = () => {
      navToggle.setAttribute('aria-expanded', 'true');
      navMenu.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    };

    navToggle.addEventListener('click', () => {
      const expanded = navToggle.getAttribute('aria-expanded') === 'true';
      expanded ? closeMenu() : openMenu();
    });

    // Close on link click (mobile)
    navMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 767.98px)').matches) closeMenu();
      });
    });

    // Close on Escape
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && navToggle.getAttribute('aria-expanded') === 'true') {
        closeMenu();
        navToggle.focus();
      }
    });

    // Close if viewport upsizes past breakpoint
    window.matchMedia('(min-width: 768px)').addEventListener('change', e => {
      if (e.matches) closeMenu();
    });
  }

  // ── Scroll reveal ──────────────────────────────────────────────────────
  const reveals = document.querySelectorAll('[data-reveal]');
  if (reveals.length) {
    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
      reveals.forEach(el => el.classList.add('is-visible'));
    } else {
      const io = new IntersectionObserver((entries, obs) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            const el    = entry.target;
            const delay = parseInt(el.dataset.revealDelay || '0', 10);
            if (delay > 0) {
              setTimeout(() => el.classList.add('is-visible'), delay);
            } else {
              el.classList.add('is-visible');
            }
            obs.unobserve(el);
          }
        }
      }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

      reveals.forEach(el => io.observe(el));
    }
  }

  // ── Smooth scroll for in-page anchors ──────────────────────────────────
  document.addEventListener('click', e => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const href = link.getAttribute('href');
    if (href === '#' || href.length < 2) return;

    const target = document.querySelector(href);
    if (!target) return;

    e.preventDefault();
    const headerOffset = (document.querySelector('[data-header]')?.offsetHeight || 0) + 12;
    const top = target.getBoundingClientRect().top + window.scrollY - headerOffset;

    window.scrollTo({
      top,
      behavior: prefersReducedMotion ? 'auto' : 'smooth',
    });

    // Update URL without jumping
    history.pushState(null, '', href);
  });
})();
