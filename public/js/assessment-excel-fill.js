(() => {
    'use strict';
    const form = document.getElementById('assessment-form');
    if (!form) return;

    const asset = form.elements.namedItem('risk_asset_id');
    const profile = form.elements.namedItem('profile');
    const fileInput = document.getElementById('assessment-workbook');
    const button = document.getElementById('excel-fill-button');
    const template = document.getElementById('excel-template-link');
    const status = document.getElementById('excel-fill-status');
    const errors = document.getElementById('excel-fill-errors');
    const descriptions = ['assessed_on', 'threat', 'vulnerability', 'existing_controls', 'risk_owner'];
    let busy = false;

    function showErrors(messages) {
        errors.replaceChildren();
        const list = document.createElement('ul');
        for (const message of messages) {
            const item = document.createElement('li');
            item.textContent = String(message);
            list.appendChild(item);
        }
        errors.appendChild(list);
        errors.hidden = false;
    }

    function syncTemplate() {
        const url = new URL(form.dataset.excelTemplateUrl, window.location.origin);
        url.searchParams.set('profile', profile.value);
        template.href = url.toString();
        status.hidden = true;
        errors.hidden = true;
    }

    async function fillForm() {
        if (busy) return;
        errors.hidden = true;
        status.hidden = true;
        if (!asset.value) {
            showErrors(['Select an asset before uploading.']);
            asset.focus();
            return;
        }
        const file = fileInput.files[0];
        if (!file) {
            showErrors(['Choose an Excel workbook first.']);
            return;
        }
        if (!/\.xlsx$/i.test(file.name) || file.size > 2 * 1024 * 1024) {
            showErrors(['Choose an .xlsx workbook no larger than 2 MB.']);
            return;
        }

        const selectedAsset = asset.value;
        const selectedProfile = profile.value;
        const keys = [...descriptions, 'c', 'i', 'a', 'likelihood',
            ...(selectedProfile === 'usim-2026' ? ['operations', 'financial', 'reputation'] : ['loss'])];
        const data = new FormData();
        data.append('_token', form.elements.namedItem('_token').value);
        data.append('risk_asset_id', selectedAsset);
        data.append('profile', selectedProfile);
        data.append('workbook', file);

        // Prevent edits while the response is pending; preserve which controls were disabled.
        const controls = Array.from(form.elements).filter(el => 'disabled' in el)
            .map(el => [el, el.disabled]);
        controls.forEach(([el]) => { el.disabled = true; });
        busy = true;
        status.textContent = 'Reading Excel values…';
        status.hidden = false;
        const abort = new AbortController();
        const timer = setTimeout(() => abort.abort(), 30000);
        try {
            const response = await fetch(form.dataset.excelFillUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: data, signal: abort.signal,
            });
            const json = response.headers.get('content-type')?.includes('application/json')
                ? await response.json() : null;
            if (!response.ok || !json) {
                const messages = json?.errors ? Object.values(json.errors).flat() : [];
                if (messages.length) {
                    showErrors(messages);
                    status.hidden = true;
                    return;
                }
                const defaults = {
                    401: 'Your session ended. Sign in again, then retry.',
                    403: 'You need an Admin or Assessor assignment in this asset’s department.',
                    404: 'The selected asset is no longer available. Refresh the assessment page.',
                    419: 'Your session expired. Refresh the page and sign in again if needed.',
                    429: 'Too many uploads. Wait one minute, then retry.',
                };
                throw new Error(defaults[response.status] || 'The upload could not be processed. Your form values have not changed.');
            }
            if (String(json.risk_asset_id) !== selectedAsset || json.profile !== selectedProfile
                || asset.value !== selectedAsset || profile.value !== selectedProfile
                || !json.fields || keys.some(key => !(key in json.fields))) {
                throw new Error('The upload does not match the current form. Please retry.');
            }
            for (const key of keys) {
                const input = form.elements.namedItem(key);
                if (input) input.value = json.fields[key] ?? '';
            }
            // Clear inactive profile inputs so an old profile's values cannot be reused accidentally.
            for (const key of (selectedProfile === 'usim-2026' ? ['loss'] : ['operations', 'financial', 'reputation'])) {
                form.elements.namedItem(key).value = '';
            }
            status.textContent = 'Excel row ' + json.row + ' filled in. Edit any values below, then click Calculate and save draft. Nothing has been saved yet.';
        } catch (error) {
            status.hidden = true;
            showErrors([error.name === 'AbortError'
                ? 'The upload timed out. Your form values have not changed. Please retry.'
                : error.message || 'The upload failed. Please retry.']);
        } finally {
            clearTimeout(timer);
            controls.forEach(([el, disabled]) => { el.disabled = disabled; });
            busy = false;
        }
    }

    profile.addEventListener('change', syncTemplate);
    asset.addEventListener('change', () => { status.hidden = true; errors.hidden = true; });
    fileInput.addEventListener('change', fillForm);
    button.addEventListener('click', fillForm);
    syncTemplate();
})();
