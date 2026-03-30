// Shared client-side validation for backoffice article forms
(function () {
    const allowed = ['image/jpeg', 'image/png', 'image/gif'];
    const maxBytes = 2_000_000;

    function validateArticleForm(form) {
        const messages = [];
        const title = form.querySelector('#title');
        const summary = form.querySelector('#summary');
        const content = form.querySelector('#content');
        const image = form.querySelector('#image');

        if (!title || !summary || !content) return messages; // form not matching expected fields

        if (!title.value.trim()) messages.push('Le titre est obligatoire.');
        if (!summary.value.trim()) messages.push('Le résumé est obligatoire.');
        if (!content.value.trim()) messages.push('Le contenu est obligatoire.');

        if (image && image.files.length) {
            const file = image.files[0];
            if (file.size > maxBytes) messages.push('Image trop volumineuse (max 2 Mo).');
            if (!allowed.includes(file.type)) messages.push('Type de fichier non autorisé.');
        }

        return messages;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form[data-validate="article"]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const messages = validateArticleForm(form);
                if (messages.length) {
                    e.preventDefault();
                    alert(messages.join('\n'));
                }
            });
        });
    });
})();
