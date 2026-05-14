/**
 * Codentra — three-scene.js
 *
 * Hero scene: a central wireframe core, a counter-rotating outer ring,
 * and a deep particle field. Designed to read as "future tech" without
 * dominating the LCP CSS gradient underneath.
 *
 * Performance contract (see CLAUDE.md §Performance Architecture):
 *   - Deferred past LCP by layouts/main.php (window.load + requestIdleCallback)
 *   - particleCount capped 800/400 (desktop/mobile)
 *   - pixelRatio capped 1.5
 *   - Pauses on IntersectionObserver + visibilitychange
 *   - Reduced-motion: scene never imported by the layout
 *
 * Exports: init(canvas)
 */

import * as THREE from 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r160/three.module.min.js';

const ACCENT      = 0x2A9D8F;
const ACCENT_SOFT = 0x36b8a8;
const CTA         = 0xF4A261;
const SECONDARY   = 0x4F5D75;

export async function init(canvas) {
  if (!canvas) return;

  const isMobile = window.innerWidth < 768;

  // ── Scene & camera ──────────────────────────────────────────────────────
  const scene  = new THREE.Scene();
  scene.fog    = new THREE.Fog(0x0A1C28, 7, 24);

  const camera = new THREE.PerspectiveCamera(58, 1, 0.1, 100);
  camera.position.set(0, 0, 9);

  // ── Renderer ────────────────────────────────────────────────────────────
  const renderer = new THREE.WebGLRenderer({
    canvas,
    antialias: true,
    alpha: true,
    powerPreference: 'low-power',
  });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
  renderer.setClearColor(0x000000, 0);

  // ── Deep particle field ─────────────────────────────────────────────────
  const particleCount = isMobile ? 400 : 800;
  const positions     = new Float32Array(particleCount * 3);
  const sizes         = new Float32Array(particleCount);

  for (let i = 0; i < particleCount; i++) {
    positions[i * 3 + 0] = (Math.random() - 0.5) * 30;
    positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
    positions[i * 3 + 2] = (Math.random() - 0.5) * 20;
    sizes[i] = Math.random();
  }

  const particleGeo = new THREE.BufferGeometry();
  particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));

  const particleMat = new THREE.PointsMaterial({
    color: ACCENT_SOFT,
    size: 0.05,
    transparent: true,
    opacity: 0.75,
    sizeAttenuation: true,
    depthWrite: false,
    blending: THREE.AdditiveBlending,
  });

  const particles = new THREE.Points(particleGeo, particleMat);
  scene.add(particles);

  // ── Central wireframe core (icosahedron) ────────────────────────────────
  const coreGeo = new THREE.IcosahedronGeometry(1.9, 0);
  const coreMat = new THREE.MeshBasicMaterial({
    color: ACCENT,
    wireframe: true,
    transparent: true,
    opacity: 0.85,
  });
  const core = new THREE.Mesh(coreGeo, coreMat);
  scene.add(core);

  // Inner solid hint for depth + slight color shift
  const coreInnerGeo = new THREE.IcosahedronGeometry(1.88, 0);
  const coreInnerMat = new THREE.MeshBasicMaterial({
    color: SECONDARY,
    transparent: true,
    opacity: 0.08,
  });
  core.add(new THREE.Mesh(coreInnerGeo, coreInnerMat));

  // ── Outer counter-rotating ring (octahedron wireframe, larger, fainter) ─
  const ringGeo = new THREE.OctahedronGeometry(3.4, 0);
  const ringMat = new THREE.MeshBasicMaterial({
    color: ACCENT_SOFT,
    wireframe: true,
    transparent: true,
    opacity: 0.28,
  });
  const ring = new THREE.Mesh(ringGeo, ringMat);
  scene.add(ring);

  // ── Accent micro-points orbiting the core (gold flecks) ─────────────────
  const fleckCount = isMobile ? 16 : 28;
  const fleckPos   = new Float32Array(fleckCount * 3);
  const fleckRadii = [];

  for (let i = 0; i < fleckCount; i++) {
    const r = 2.4 + Math.random() * 1.2;
    const a = Math.random() * Math.PI * 2;
    const y = (Math.random() - 0.5) * 1.8;
    fleckPos[i * 3 + 0] = Math.cos(a) * r;
    fleckPos[i * 3 + 1] = y;
    fleckPos[i * 3 + 2] = Math.sin(a) * r;
    fleckRadii.push({ r, a, y });
  }

  const fleckGeo = new THREE.BufferGeometry();
  fleckGeo.setAttribute('position', new THREE.BufferAttribute(fleckPos, 3));
  const fleckMat = new THREE.PointsMaterial({
    color: CTA,
    size: 0.11,
    transparent: true,
    opacity: 0.9,
    sizeAttenuation: true,
    depthWrite: false,
    blending: THREE.AdditiveBlending,
  });
  const flecks = new THREE.Points(fleckGeo, fleckMat);
  scene.add(flecks);

  // ── Mouse parallax (subtle) ─────────────────────────────────────────────
  const target = { x: 0, y: 0 };
  const ease   = { x: 0, y: 0 };

  window.addEventListener('mousemove', e => {
    target.x = (e.clientX / window.innerWidth  - 0.5) * 0.5;
    target.y = (e.clientY / window.innerHeight - 0.5) * 0.5;
  }, { passive: true });

  // ── Resize ──────────────────────────────────────────────────────────────
  const resize = () => {
    const { clientWidth: w, clientHeight: h } = canvas;
    if (w === 0 || h === 0) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  };
  resize();
  window.addEventListener('resize', resize);

  // ── Pause when off-screen or tab hidden ────────────────────────────────
  let isVisible = true;
  let tabActive = !document.hidden;
  const visIo = new IntersectionObserver(entries => {
    isVisible = entries[0].isIntersecting;
  });
  visIo.observe(canvas);

  const onVisibility = () => { tabActive = !document.hidden; };
  document.addEventListener('visibilitychange', onVisibility);

  // ── Animate ─────────────────────────────────────────────────────────────
  const clock = new THREE.Clock();
  let rafId;
  const fleckPositionAttr = flecks.geometry.getAttribute('position');

  const animate = () => {
    rafId = requestAnimationFrame(animate);
    if (!isVisible || !tabActive) return;

    const t = clock.getElapsedTime();

    ease.x += (target.x - ease.x) * 0.05;
    ease.y += (target.y - ease.y) * 0.05;

    // Core: slow tumble + float
    core.rotation.x = t * 0.18 + ease.y * 0.6;
    core.rotation.y = t * 0.22 + ease.x * 0.6;
    core.position.y = Math.sin(t * 0.55) * 0.14;

    // Outer ring: counter-rotate, slight tilt
    ring.rotation.x = -t * 0.10 + ease.y * 0.3;
    ring.rotation.y = -t * 0.14 - ease.x * 0.3;
    ring.rotation.z = Math.sin(t * 0.2) * 0.08;

    // Flecks orbit on their own
    for (let i = 0; i < fleckCount; i++) {
      const f = fleckRadii[i];
      const angle = f.a + t * 0.35;
      fleckPositionAttr.setXYZ(i, Math.cos(angle) * f.r, f.y + Math.sin(t * 0.6 + i) * 0.05, Math.sin(angle) * f.r);
    }
    fleckPositionAttr.needsUpdate = true;

    // Particle field: slow drift + parallax
    particles.rotation.y = t * 0.025 + ease.x * 0.12;
    particles.rotation.x = ease.y * 0.08;

    // Subtle camera parallax for depth
    camera.position.x = ease.x * 0.4;
    camera.position.y = -ease.y * 0.3;
    camera.lookAt(0, 0, 0);

    renderer.render(scene, camera);
  };

  // Defer first paint by one frame so the main thread can finish settling
  requestAnimationFrame(animate);

  // ── Cleanup ─────────────────────────────────────────────────────────────
  return () => {
    cancelAnimationFrame(rafId);
    window.removeEventListener('resize', resize);
    document.removeEventListener('visibilitychange', onVisibility);
    visIo.disconnect();
    particleGeo.dispose(); particleMat.dispose();
    coreGeo.dispose();     coreMat.dispose();
    coreInnerGeo.dispose(); coreInnerMat.dispose();
    ringGeo.dispose();     ringMat.dispose();
    fleckGeo.dispose();    fleckMat.dispose();
    renderer.dispose();
  };
}
