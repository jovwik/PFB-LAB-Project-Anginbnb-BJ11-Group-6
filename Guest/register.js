document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); 

    
    var nameInput = document.getElementById('Full-Name');
    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');

    var name = nameInput.value;
    var email = emailInput.value;
    var password = passwordInput.value;
    
    
    var role = "Member"; 

    var errorMessage = "";

    
    if (name === "") {
        errorMessage = "Name must be filled.";
    } else if (name.length < 3 || name.length > 50) {
        errorMessage = "Name must be between 3 to 50 characters long.";
    } else if (!isLettersAndSpaces(name)) {
        errorMessage = "Name must only contain letters and spaces.";
    }

    
    else if (email === "") {
        errorMessage = "Email must be filled.";
    } else if (!isValidEmailCustom(email)) {
        
        errorMessage = "Invalid email format (Check '@', '.', position, and symbols)."; 
    }

    
    else if (password === "") {
        errorMessage = "Password must be filled.";
    } else if (password.length < 8) {
        errorMessage = "Password must be at least 8 characters long.";
    } else if (!hasUpperCase(password)) {
        errorMessage = "Password must contain at least 1 uppercase letter.";
    } else if (!hasLowerCase(password)) {
        errorMessage = "Password must contain at least 1 lowercase letter.";
    } else if (!hasNumber(password)) {
        errorMessage = "Password must contain at least 1 number.";
    } else if (!hasSpecialChar(password)) {
        errorMessage = "Password must contain at least 1 special character (!, @, #, etc).";
    }

    
    if (errorMessage !== "") {
        
        alert("Error: " + errorMessage);
    } else {
        alert("Registration Successful!\n\nWelcome, " + name + "\nRole: " + role);
        
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

    
    if (email.includes('@@')) return false;
    if (email.includes('..')) return false;
    if (email.includes('@.')) return false;
    if (email.includes('.@')) return false;

    return true;
}

function hasUpperCase(str) {
    for (var i = 0; i < str.length; i++) {
        if (str[i] >= 'A' && str[i] <= 'Z') return true;
    }
    return false;
}

function hasLowerCase(str) {
    for (var i = 0; i < str.length; i++) {
        if (str[i] >= 'a' && str[i] <= 'z') return true;
    }
    return false;
}

function hasNumber(str) {
    for (var i = 0; i < str.length; i++) {
        if (str[i] >= '0' && str[i] <= '9') return true;
    }
    return false;
}

function hasSpecialChar(str) {
    var specials = "!@#$%^&*(),.?\":{}|<>";
    for (var i = 0; i < str.length; i++) {
        if (specials.includes(str[i])) return true;
    }
    return false;
}