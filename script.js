// Sticky Navigation
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 50) {
    navbar.classList.add('sticky');
  } else {
    navbar.classList.remove('sticky');
  }
});

// Mobile Menu Toggle
const menuToggler = document.getElementById('menu-toggler');
const navLinks = document.querySelector('.nav-links');

menuToggler.addEventListener('click', () => {
  navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
});