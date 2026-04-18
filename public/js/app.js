/**
 * CheckMyFitness — Application Scripts
 * Loaded by layouts/app.blade.php for all authenticated pages.
 */

/**
 * Toggle the sidebar open/closed (mobile).
 */
function toggleSB() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sb-overlay').classList.toggle('on');
}

/**
 * Close the sidebar (e.g. when the overlay is tapped).
 */
function closeSB() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sb-overlay').classList.remove('on');
}
