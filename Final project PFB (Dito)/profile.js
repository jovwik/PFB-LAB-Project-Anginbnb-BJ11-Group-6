document.addEventListener("DOMContentLoaded", function() {
    
    
    
    
    let dbCurrentPassword = "Password123!"; 

    
    
    const forms = document.querySelectorAll('form');
    const profileForm = forms[0]; 
    const passwordForm = forms[1]; 
    const deleteBtn = document.querySelector('.btn-danger'); 

    
    
    
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();

       
        const nameInput = document.getElementById('fullname');
        const emailInput = document.getElementById('email');
        
        const name = nameInput.value;
        const email = emailInput.value;
        let errorMessage = "";

      
        if (name === "") {
            errorMessage = "Name must be filled.";
        } else if (name.length < 3 || name.length > 50) {
            errorMessage = "Name must be between 3 to 50 characters.";
        } else if (!isLettersAndSpaces(name)) {
            errorMessage = "Name must only contain letters and spaces.";
        }

      
        else if (email === "") {
            errorMessage = "Email must be filled.";
        } else if (!isValidEmailCustom(email)) {
            errorMessage = "Invalid email format (Check '@', '.', position, and symbols).";
        }

       
        if (errorMessage) {
            alert("Update Failed: " + errorMessage);
        } else {
            alert("Success! Profile updated.");
        }
    });


    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const currentPassInput = document.getElementById('current-pass');
        const newPassInput = document.getElementById('new-pass');
        const confirmPassInput = document.getElementById('confirm-pass');

        const currentPass = currentPassInput.value;
        const newPass = newPassInput.value;
        const confirmPass = confirmPassInput.value;
        let errorMessage = "";

       
        if (currentPass === "") {
            errorMessage = "Current password must be filled.";
        } else if (currentPass !== dbCurrentPassword) {
            errorMessage = "Current password does not match our records.";
        }
        
       
        else if (newPass === "") {
            errorMessage = "New password must be filled.";
        } else if (newPass.length < 8) {
            errorMessage = "New password must be at least 8 characters.";
        } else if (!hasComplexRequirements(newPass)) {
            errorMessage = "New password must contain Uppercase, Lowercase, Number, and Special Character.";
        }

        
        else if (newPass !== confirmPass) {
            errorMessage = "Confirm password does not match new password.";
        }

       
        if (errorMessage) {
            alert("Password Update Failed: " + errorMessage);
        } else {
            
            dbCurrentPassword = newPass; 
            alert("Success! Password changed successfully.");
            
            
            passwordForm.reset();
        }
    });

    
    
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            
            const isConfirmed = confirm("Are you sure you want to delete your account? This action cannot be undone.");

            if (isConfirmed) {
                alert("Account deleted successfully.");
               
                window.location.href = "index.html"; 
            }
        });
    }

});






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
    
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return hasUpper && hasLower && hasNumber && hasSpecial;
}