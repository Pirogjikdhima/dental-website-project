let inactivityTime = 900000; // 15 minuta
let timeout;

function resetTimeout() {
    clearTimeout(timeout);
    timeout = setTimeout(logout, inactivityTime);
}

function logout() {
    $.ajax({
        url: 'http://localhost/DetyreKursi/connection.php',
        type: 'POST',
        data: {action: 'logout'},
        success: function (response) {
            if (response.success) {
                window.location.href = response.location;
            }
        },
        error: function (error) {
            console.error("Logout request failed: " + error);
        }
    });
}

window.onload = resetTimeout;
document.onmousemove = resetTimeout;
document.addEventListener('keydown', resetTimeout);
document.onclick = resetTimeout;
