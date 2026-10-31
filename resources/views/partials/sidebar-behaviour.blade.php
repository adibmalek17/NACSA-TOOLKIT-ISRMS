<script>
(() => {
    const root = document.documentElement;
    const button = document.getElementById('sidebar-toggle');
    const backdrop = document.getElementById('sidebar-backdrop');
    function setCollapsed(collapsed, remember = false) {
        root.classList.toggle('nav-collapsed', collapsed);
        button.setAttribute('aria-expanded', String(!collapsed));
        const label = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
        button.setAttribute('aria-label', label);
        button.title = label + ' (Ctrl+Shift+S)';
        if (remember) { try { localStorage.setItem('nacsa.sidebar.closed', String(collapsed)); } catch (_) {} }
    }
    function toggle() { setCollapsed(!root.classList.contains('nav-collapsed'), true); }
    setCollapsed(root.classList.contains('nav-collapsed'));
    button.addEventListener('click', toggle);
    backdrop.addEventListener('click', () => { setCollapsed(true, true); button.focus(); });
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key.toLowerCase() === 's') {
            event.preventDefault(); toggle(); button.focus();
        }
        if (event.key === 'Escape' && !root.classList.contains('nav-collapsed')) {
            setCollapsed(true, true); button.focus();
        }
    });
})();
</script>
