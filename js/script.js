// js/script.js
// ฟังก์ชันสำหรับแสดงผลข้อความจาก URL parameters (ถ้ามี)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const errorMessageDiv = document.getElementById('errorMessage'); // หากมี
    const successMessageDiv = document.querySelector('.success-message'); // หากมี

    if (message) {
        if (message.includes('success')) {
            if (successMessageDiv) {
                successMessageDiv.textContent = 'ดำเนินการสำเร็จ: ' + message.replace('_success', ' ').replace(/_/g, ' ');
                successMessageDiv.style.display = 'block';
            } else {
                // Fallback to alert if no specific div
                alert('ดำเนินการสำเร็จ: ' + message.replace('_success', ' ').replace(/_/g, ' '));
            }
        } else if (message.includes('error') || message.includes('invalid') || message.includes('duplicate')) {
            if (errorMessageDiv) {
                errorMessageDiv.textContent = 'เกิดข้อผิดพลาด: ' + message.replace(/_/g, ' ');
                errorMessageDiv.style.display = 'block';
            } else {
                // Fallback to alert if no specific div
                alert('เกิดข้อผิดพลาด: ' + message.replace(/_/g, ' '));
            }
        }
    }
});