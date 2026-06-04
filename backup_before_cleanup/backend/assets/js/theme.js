(function () {
    const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme);

    window.toggleTheme = function () {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', newTheme);
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleIcons(newTheme);
    };

    function updateToggleIcons(theme) {
        const icons = document.querySelectorAll('.theme-toggle i');
        icons.forEach(icon => {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }

    // Initialize icons on load
    document.addEventListener('DOMContentLoaded', () => {
        updateToggleIcons(document.documentElement.getAttribute('data-theme'));
    });
})();
