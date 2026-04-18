/**
 * CheckMyFitness — Session Detail Page Scripts
 * Handles the expandable student health record accordion.
 */

/**
 * Toggle a student's checkup detail panel open or closed.
 *
 * @param {number} id - The checkup ID used to locate the panel and arrow elements.
 */
function toggleCk(id) {
    var panel = document.getElementById('ck-' + id);
    var arrow = document.getElementById('arr-' + id);
    if (!panel) return;

    var isOpen = panel.style.display !== 'none';
    panel.style.display = isOpen ? 'none' : 'block';
    if (arrow) arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
}
