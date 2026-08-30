const header = document.querySelector('[data-header]');
const toggle = document.querySelector('[data-nav-toggle]');
const menu = document.querySelector('[data-nav-menu]');
const year = document.querySelector('[data-year]');

if (year) {
  year.textContent = new Date().getFullYear();
}

const setHeaderState = () => {
  header?.classList.toggle('scrolled', window.scrollY > 8);
};

setHeaderState();
window.addEventListener('scroll', setHeaderState, { passive: true });

toggle?.addEventListener('click', () => {
  const isOpen = menu?.classList.toggle('open') ?? false;
  toggle.setAttribute('aria-expanded', String(isOpen));
});

menu?.addEventListener('click', (event) => {
  if (event.target instanceof HTMLAnchorElement) {
    menu.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
  }
});

window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    menu?.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
  }
});
