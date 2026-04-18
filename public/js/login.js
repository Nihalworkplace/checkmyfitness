/**
 * CheckMyFitness — Login Page Scripts
 */

/**
 * Switch between the three role panels (Parent / Doctor / Admin).
 * Also updates the URL query string so the correct tab is pre-selected
 * if the page is refreshed or a validation error is returned.
 *
 * @param {string} role  - 'parent' | 'doctor' | 'admin'
 * @param {HTMLElement} clickedTab
 */
function switchRole(role, clickedTab) {
    document.querySelectorAll('.role-tab').forEach(tab => tab.classList.remove('active'));
    clickedTab.classList.add('active');

    document.querySelectorAll('.panel').forEach(panel => panel.classList.remove('active'));
    document.getElementById('panel-' + role).classList.add('active');

    history.replaceState(null, '', '?role=' + role);
}

/**
 * Switch between the two parent login methods (email/password vs reference code).
 *
 * @param {string} type - 'email' | 'code'
 */
function switchLoginType(type) {
    const isEmail = type === 'email';

    document.getElementById('form-email').style.display = isEmail ? 'block' : 'none';
    document.getElementById('form-code').style.display  = isEmail ? 'none'  : 'block';

    document.getElementById('lt-email').classList.toggle('active',  isEmail);
    document.getElementById('lt-code').classList.toggle('active',  !isEmail);
}

/**
 * Auto-uppercase inputs that carry the `form-input--uppercase` class.
 * Applied once on DOMContentLoaded so it covers all matching fields.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-input--uppercase').forEach(function (el) {
        el.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    });
});
