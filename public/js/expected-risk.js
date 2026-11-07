(() => {
    'use strict';
    document.querySelectorAll('[data-expected-risk]').forEach(panel => {
        const form = panel.closest('form');
        const profileSelect = form.elements.namedItem('profile');
        const profile = () => profileSelect?.value || panel.dataset.profile;
        const labels = [...panel.querySelectorAll('[data-expected-key]')];
        function sync() {
            const usim = profile() === 'usim-2026';
            const maximum = profile() === 'nacsa-5-2024' ? '5' : '3';
            const hint = panel.querySelector('[data-expected-hint]');
            if (hint) hint.textContent = `Enter whole numbers from 1 to ${maximum} for this profile.`;
            labels.forEach(label => {
                const key = label.dataset.expectedKey;
                const active = key === 'loss' ? !usim : !['operations','financial','reputation'].includes(key) || usim;
                label.hidden = !active;
                const input = label.querySelector('input');
                input.disabled = !active;
                input.max = maximum;
            });
            const active = labels.map(label => label.querySelector('input')).filter(input => !input.disabled);
            const any = active.some(input => input.value !== '');
            active.forEach(input => { input.required = any; });
        }
        panel.addEventListener('input', sync);
        form.addEventListener('expected-filled', sync);
        profileSelect?.addEventListener('change', () => {
            labels.forEach(label => { label.querySelector('input').value = ''; });
            sync();
        });
        sync();
    });
})();
