/**
 * CheckMyFitness — Application Scripts
 * Loaded by layouts/app.blade.php for all authenticated pages.
 */

function toggleSB() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sb-overlay').classList.toggle('on');
}

function closeSB() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sb-overlay').classList.remove('on');
}

// Phone-number input: digits only, max 10 characters.
// Add class="phone-input" to any input to activate.
(function () {
    function bindPhoneInputs() {
        document.querySelectorAll('.phone-input').forEach(function (el) {
            if (el.dataset.phoneInit) return;
            el.dataset.phoneInit = '1';

            // Block non-digit keys immediately (desktop keyboards)
            el.addEventListener('keydown', function (e) {
                var ctrl = e.ctrlKey || e.metaKey;
                if (ctrl) return; // allow Ctrl+A, Ctrl+C, Ctrl+V etc.
                var nav = ['Backspace','Delete','Tab','Enter','ArrowLeft','ArrowRight','Home','End'];
                if (nav.indexOf(e.key) !== -1) return;
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                    return;
                }
                // Block if already 10 digits and no selection to replace
                if (el.value.length >= 10 && el.selectionStart === el.selectionEnd) {
                    e.preventDefault();
                }
            });

            // Strip any non-digits that slipped through (mobile / IME / autofill)
            el.addEventListener('input', function () {
                var clean = el.value.replace(/\D/g, '').slice(0, 10);
                el.value = clean;
            });

            // Strip non-digits from paste
            el.addEventListener('paste', function (e) {
                e.preventDefault();
                var text = (e.clipboardData || window.clipboardData).getData('text');
                el.value = text.replace(/\D/g, '').slice(0, 10);
            });
        });
    }

    // Run now (script loads after page content) and again after full parse
    bindPhoneInputs();
    document.addEventListener('DOMContentLoaded', bindPhoneInputs);
}());
