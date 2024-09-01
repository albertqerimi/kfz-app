$(document).ready(function() {
    function hideAlertsAfterDelay(selector, delay) {
        setTimeout(function () {
            const alerts = document.querySelectorAll(selector);
            alerts.forEach(alert => {
                alert.classList.add('fade'); 
                setTimeout(() => alert.remove(), 350);
            });
        }, delay);
    }
    hideAlertsAfterDelay('.alert', 2000);
});