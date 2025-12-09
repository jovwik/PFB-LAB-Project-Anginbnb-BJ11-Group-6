document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. SIMULASI DATABASE ---
    // Karena tidak ada backend nyata, kita buat password 'asli' di sini
    // untuk pengecekan "Current Password must be the same as in database"
    let dbCurrentPassword = "Password123!"; 

    // --- 2. SELEKSI ELEMEN (Tanpa mengubah HTML) ---
    // Kita ambil form berdasarkan urutannya di halaman
    const forms = document.querySelectorAll('form');
    const profileForm = forms[0]; // Form pertama (Update Profile)
    const passwordForm = forms[1]; // Form kedua (Change Password)
    const deleteBtn = document.querySelector('.btn-danger'); // Tombol Delete Account

    // ============================================================
    // LOGIKA 1: UPDATE PROFILE (Name & Email)
    // ============================================================
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Ambil value input
        const nameInput = document.getElementById('fullname');
        const emailInput = document.getElementById('email');
        
        const name = nameInput.value;
        const email = emailInput.value;
        let errorMessage = "";

        // --- Validasi Name (Sesuai Register) ---
        if (name === "") {
            errorMessage = "Name must be filled.";
        } else if (name.length < 3 || name.length > 50) {
            errorMessage = "Name must be between 3 to 50 characters.";
        } else if (!isLettersAndSpaces(name)) {
            errorMessage = "Name must only contain letters and spaces.";
        }

        // --- Validasi Email (Sesuai Register) ---
        else if (email === "") {
            errorMessage = "Email must be filled.";
        } else if (!isValidEmailCustom(email)) {
            errorMessage = "Invalid email format (Check '@', '.', position, and symbols).";
        }

        // --- Hasil ---
        if (errorMessage) {
            alert("Update Failed: " + errorMessage);
        } else {
            alert("Success! Profile updated.");
        }
    });

    // ============================================================
    // LOGIKA 2: CHANGE PASSWORD
    // ============================================================
    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const currentPassInput = document.getElementById('current-pass');
        const newPassInput = document.getElementById('new-pass');
        const confirmPassInput = document.getElementById('confirm-pass');

        const currentPass = currentPassInput.value;
        const newPass = newPassInput.value;
        const confirmPass = confirmPassInput.value;
        let errorMessage = "";

        // --- Cek 1: Current Password harus sama dengan Database ---
        if (currentPass === "") {
            errorMessage = "Current password must be filled.";
        } else if (currentPass !== dbCurrentPassword) {
            errorMessage = "Current password does not match our records.";
        }
        
        // --- Cek 2: Validasi New Password (Sesuai Register) ---
        else if (newPass === "") {
            errorMessage = "New password must be filled.";
        } else if (newPass.length < 8) {
            errorMessage = "New password must be at least 8 characters.";
        } else if (!hasComplexRequirements(newPass)) {
            errorMessage = "New password must contain Uppercase, Lowercase, Number, and Special Character.";
        }

        // --- Cek 3: Confirm New Password harus sama dengan New Password ---
        else if (newPass !== confirmPass) {
            errorMessage = "Confirm password does not match new password.";
        }

        // --- Hasil ---
        if (errorMessage) {
            alert("Password Update Failed: " + errorMessage);
        } else {
            // Update password di "database" simulasi
            dbCurrentPassword = newPass; 
            alert("Success! Password changed successfully.");
            
            // Reset form
            passwordForm.reset();
        }
    });

    // ============================================================
    // LOGIKA 3: DELETE ACCOUNT
    // ============================================================
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            // Tampilkan Popup Konfirmasi
            const isConfirmed = confirm("Are you sure you want to delete your account? This action cannot be undone.");

            if (isConfirmed) {
                alert("Account deleted successfully.");
                // Log out & Redirect ke halaman login
                window.location.href = "index.html"; // Pastikan nama file login kamu index.html atau login.html
            }
        });
    }

});


// ============================================================
// HELPER FUNCTIONS (Fungsi Validasi Register yg dipakai ulang)
// ============================================================

function isLettersAndSpaces(str) {
    for (var i = 0; i < str.length; i++) {
        var char = str[i];
        if (!(char >= 'a' && char <= 'z') && 
            !(char >= 'A' && char <= 'Z') && 
            char !== ' ') {
            return false;
        }
    }
    return true;
}

function isValidEmailCustom(email) {
    var atParts = email.split('@');
    if (atParts.length !== 2) return false; 
    if (email.indexOf('.') === -1) return false;
    if (email.startsWith('@') || email.startsWith('.')) return false;
    if (email.endsWith('@') || email.endsWith('.')) return false;
    if (email.includes('@@') || email.includes('..') || email.includes('@.') || email.includes('.@')) return false;
    return true;
}

function hasComplexRequirements(password) {
    // Gabungan cek Upper, Lower, Number, Special
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return hasUpper && hasLower && hasNumber && hasSpecial;
}