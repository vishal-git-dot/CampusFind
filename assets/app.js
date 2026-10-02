(() => {
  const root = document.documentElement;
  const saved = localStorage.getItem('campusfind-theme');
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
  root.dataset.theme = saved === 'dark' || saved === 'light' ? saved : (prefersDark ? 'dark' : 'light');

  const updateThemeButton = () => {
    const dark = root.dataset.theme === 'dark';
    const icon = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');
    const toggle = document.getElementById('themeToggle');
    if (icon) icon.textContent = dark ? '☀' : '☾';
    if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
    if (toggle) toggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
  };
  updateThemeButton();

  document.getElementById('themeToggle')?.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('campusfind-theme', root.dataset.theme);
    updateThemeButton();
  });

  const menuBtn = document.getElementById('mobileMenuBtn');
  const menu = document.getElementById('mobileMenu');
  menuBtn?.addEventListener('click', () => {
    const open = menu?.classList.toggle('open') ?? false;
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  });

  menu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    menu.classList.remove('open');
    menuBtn?.setAttribute('aria-expanded', 'false');
  }));

  const fileInput = document.getElementById('imageInput');
  const fileName = document.getElementById('fileName');
  const uploadZone = document.getElementById('uploadZone');
  fileInput?.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (fileName) fileName.textContent = file ? `Selected: ${file.name}` : '';
    uploadZone?.classList.toggle('has-file', Boolean(file));
  });

  document.querySelectorAll('[data-toast]').forEach(toast => {
    window.setTimeout(() => {
      toast.classList.add('hide');
      window.setTimeout(() => toast.remove(), 300);
    }, 4500);
  });

  const reveal = () => {
    document.querySelectorAll('.reveal').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight - 40) el.classList.add('visible');
    });
  };

  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
  } else {
    window.addEventListener('scroll', reveal, { passive: true });
    window.requestAnimationFrame(reveal);
  }
})();
