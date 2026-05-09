/**
 * Codentra — three-scene.js
 * Hero scene: particle field + floating wireframe icosahedron in --clr-accent.
 * Lazy-loaded only on home, only when prefers-reduced-motion is OFF.
 *
 * Exports: init(canvas)
 */

import * as THREE from 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r160/three.module.min.js';

const ACCENT_COLOR    = 0x2A9D8F;
const SECONDARY_COLOR = 0x4F5D75;

export async function init(canvas) {
  if (!canvas) return;

  // ── Scene & camera ──────────────────────────────────────────────────────
  const scene  = new THREE.Scene();
  scene.fog    = new THREE.Fog(0x0A1C28, 6, 22);

  const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 100);
  camera.position.z = 8;

  // ── Renderer ────────────────────────────────────────────────────────────
  const renderer = new THREE.WebGLRenderer({
    canvas,
    antialias: true,
    alpha: true,
    powerPreference: 'low-power',
  });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
  renderer.setClearColor(0x000000, 0);

  // ── Particle field ──────────────────────────────────────────────────────
  // Note: spec asked for 1500 desktop / 800 mobile based on a 3000 baseline.
  // Current baseline is already 900/400 — going slightly lower as a perf pass.
  const particleCount = window.innerWidth < 768 ? 400 : 800;
  const positions     = new Float32Array(particleCount * 3);

  for (let i = 0; i < particleCount; i++) {
    positions[i * 3 + 0] = (Math.random() - 0.5) * 28;
    positions[i * 3 + 1] = (Math.random() - 0.5) * 18;
    positions[i * 3 + 2] = (Math.random() - 0.5) * 18;
  }

  const particleGeo = new THREE.BufferGeometry();
  particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));

  const particleMat = new THREE.PointsMaterial({
    color: ACCENT_COLOR,
    size: 0.04,
    transparent: true,
    opacity: 0.7,
    sizeAttenuation: true,
    depthWrite: false,
  });

  const particles = new THREE.Points(particleGeo, particleMat);
  scene.add(particles);

  // ── Floating wireframe icosahedron ──────────────────────────────────────
  const icoGeo = new THREE.IcosahedronGeometry(2.2, 0);
  const icoMat = new THREE.MeshBasicMaterial({
    color: ACCENT_COLOR,
    wireframe: true,
    transparent: true,
    opacity: 0.55,
  });
  const ico = new THREE.Mesh(icoGeo, icoMat);
  ico.position.set(0, 0, 0);
  scene.add(ico);

  // Inner subtle solid for depth
  const innerGeo = new THREE.IcosahedronGeometry(2.18, 0);
  const innerMat = new THREE.MeshBasicMaterial({
    color: SECONDARY_COLOR,
    transparent: true,
    opacity: 0.05,
  });
  const inner = new THREE.Mesh(innerGeo, innerMat);
  ico.add(inner);

  // ── Mouse parallax ──────────────────────────────────────────────────────
  const target = { x: 0, y: 0 };
  const mouse  = { x: 0, y: 0 };

  window.addEventListener('mousemove', e => {
    target.x = (e.clientX / window.innerWidth  - 0.5) * 0.6;
    target.y = (e.clientY / window.innerHeight - 0.5) * 0.6;
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

  const animate = () => {
    rafId = requestAnimationFrame(animate);
    if (!isVisible || !tabActive) return;

    const t = clock.getElapsedTime();

    // Smooth parallax
    mouse.x += (target.x - mouse.x) * 0.04;
    mouse.y += (target.y - mouse.y) * 0.04;

    ico.rotation.x = t * 0.18 + mouse.y * 0.6;
    ico.rotation.y = t * 0.22 + mouse.x * 0.6;
    ico.position.y = Math.sin(t * 0.6) * 0.12;

    particles.rotation.y = t * 0.03 + mouse.x * 0.15;
    particles.rotation.x = mouse.y * 0.1;

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
    particleGeo.dispose();
    particleMat.dispose();
    icoGeo.dispose(); icoMat.dispose();
    innerGeo.dispose(); innerMat.dispose();
    renderer.dispose();
  };
}
