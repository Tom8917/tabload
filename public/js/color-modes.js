/*!
* Color mode toggler for CoreUI's docs (https://coreui.io/)
* Copyright (c) 2024 creativeLabs Łukasz Holeczek
* Licensed under the Creative Commons Attribution 3.0 Unported License.
*/
(() => {
  const THEME = 'coreui-free-bootstrap-admin-template-theme';
  const ICON_CLASS_MAP = {
    light: 'fa-sun',
    dark: 'fa-moon',
    auto: 'fa-circle-half-stroke'
  };

  const getStoredTheme = () => localStorage.getItem(THEME);
  const setStoredTheme = theme => localStorage.setItem(THEME, theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = theme => {
    const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const activeTheme = theme === 'auto' ? (isDarkMode ? 'dark' : 'light') : theme;

    document.documentElement.setAttribute('data-coreui-theme', activeTheme);

    // Dispatch custom event
    const event = new Event('ColorSchemeChange');
    document.documentElement.dispatchEvent(event);
  };

  const showActiveTheme = theme => {
    const activeThemeIcon = document.querySelector('.theme-icon-active');
    const btnToActive = document.querySelector(`[data-coreui-theme-value="${theme}"]`);
    const currentTheme = theme === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;

    // Update active button state
    for (const element of document.querySelectorAll('[data-coreui-theme-value]')) {
      element.classList.remove('active');
    }
    btnToActive.classList.add('active');

    // Update the active theme icon
    for (const cls of Object.values(ICON_CLASS_MAP)) {
      activeThemeIcon.classList.remove(cls);
    }
    activeThemeIcon.classList.add(ICON_CLASS_MAP[currentTheme]);
  };

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = getStoredTheme();
    if (!['light', 'dark'].includes(storedTheme)) {
      setTheme(getPreferredTheme());
      showActiveTheme('auto');
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);
    showActiveTheme(preferredTheme);

    for (const toggle of document.querySelectorAll('[data-coreui-theme-value]')) {
      toggle.addEventListener('click', () => {
        const theme = toggle.getAttribute('data-coreui-theme-value');
        setStoredTheme(theme);
        setTheme(theme);
        showActiveTheme(theme);
      });
    }
  });
})();

//# sourceMappingURL=color-modes.js.map