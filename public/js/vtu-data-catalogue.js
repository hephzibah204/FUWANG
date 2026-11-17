(() => {
    const form = document.querySelector('[data-data-catalogue]');
    if (!form) return;
    const rows = form.querySelector('[data-catalogue-rows]');
    const template = rows.firstElementChild.cloneNode(true);
    let index = rows.children.length;
    form.querySelector('[data-add-plan]').addEventListener('click', () => {
        const row = template.cloneNode(true);
        row.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/plans\[\d+\]/, `plans[${index}]`);
            input.value = input.tagName === 'SELECT' ? 'MTN' : '';
        });
        index++;
        rows.append(row);
        row.querySelector('input').focus();
    });
    rows.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-plan]');
        if (!button) return;
        if (rows.children.length > 1) button.closest('[data-catalogue-row]').remove();
        else rows.querySelectorAll('input').forEach(input => { input.value = ''; });
    });
})();
