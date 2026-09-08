import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const swalDefaults = {
    confirmButtonColor: '#1e3a8a',
    cancelButtonColor: '#6b7280',
    customClass: {
        confirmButton: 'rounded-lg px-4 py-2 text-sm font-semibold',
        cancelButton: 'rounded-lg px-4 py-2 text-sm font-semibold',
    },
};

window.Swal = Swal;

function showSwal({ icon = 'info', title = '', text = '', html = '' }) {
    return Swal.fire({
        icon,
        title,
        text: html ? undefined : text,
        html: html || undefined,
        ...swalDefaults,
    });
}

async function showFlashQueue(queue) {
    for (const item of queue) {
        await showSwal(item);
    }
}

function initFlashFromDom() {
    const el = document.getElementById('app-swal-flash');
    if (!el) {
        return;
    }

    try {
        const queue = JSON.parse(el.textContent || '[]');
        if (Array.isArray(queue) && queue.length > 0) {
            showFlashQueue(queue);
        }
    } catch {
        // Abaikan JSON rosak.
    }
}

function migrateLegacyConfirmForms() {
    document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
        const attr = form.getAttribute('onsubmit') || '';
        const match = attr.match(/confirm\(\s*['"](.+?)['"]\s*\)/);

        if (match) {
            form.removeAttribute('onsubmit');
            form.dataset.swalConfirm = match[1];
        }
    });
}

function initConfirmForms() {
    migrateLegacyConfirmForms();

    document.querySelectorAll('form[data-swal-confirm]').forEach((form) => {
        if (form.dataset.swalBound === '1') {
            return;
        }

        form.dataset.swalBound = '1';

        form.addEventListener('submit', async (event) => {
            if (form.dataset.swalConfirmed === '1') {
                form.dataset.swalConfirmed = '0';
                return;
            }

            event.preventDefault();

            const result = await Swal.fire({
                icon: form.dataset.swalIcon || 'question',
                title: form.dataset.swalTitle || 'Pengesahan',
                text: form.getAttribute('data-swal-confirm') || '',
                showCancelButton: true,
                confirmButtonText: form.dataset.swalConfirmText || 'Ya, teruskan',
                cancelButtonText: form.dataset.swalCancelText || 'Batal',
                ...swalDefaults,
            });

            if (result.isConfirmed) {
                form.dataset.swalConfirmed = '1';
                form.requestSubmit();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initFlashFromDom();
    initConfirmForms();
});

export { showSwal, initFlashFromDom, initConfirmForms };
