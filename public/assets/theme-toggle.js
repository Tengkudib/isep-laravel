(function () {
    var saved = localStorage.getItem('isep-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();

function isepGetLangCookie() {
    var m = document.cookie.match(/(?:^|;\s*)isep_lang=(ms|en)/);
    return m ? m[1] : 'ms';
}

function isepThemeLabel(mode) {
    var isEn = isepGetLangCookie() === 'en';
    if (mode === 'dark') return isEn ? 'Light Mode' : 'Mod Terang';
    return isEn ? 'Dark Mode' : 'Mod Gelap';
}

function toggleIsepTheme() {
    var current = document.documentElement.getAttribute('data-theme') || 'light';
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('isep-theme', next);

    var icon = document.getElementById('themeToggleIcon');
    var label = document.getElementById('themeToggleLabel');
    if (icon) icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    if (label) label.textContent = isepThemeLabel(next);
}

function toggleIsepLang() {
    var next = isepGetLangCookie() === 'en' ? 'ms' : 'en';
    document.cookie = 'isep_lang=' + next + ';path=/;max-age=31536000';
    location.reload();
}

document.addEventListener('DOMContentLoaded', function () {
    var current = document.documentElement.getAttribute('data-theme') || 'light';
    var icon = document.getElementById('themeToggleIcon');
    var label = document.getElementById('themeToggleLabel');
    if (icon) icon.className = current === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    if (label) label.textContent = isepThemeLabel(current);
});