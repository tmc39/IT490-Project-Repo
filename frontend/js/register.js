// runs when the register form is submitted
// if it returns false, the form will not be submitted.
function validateRegisterForm() {

    // get the values from the form fields
    const firstName = document.getElementById("firstname").value.trim();
    const lastName = document.getElementById("lastname").value.trim();
    const email = document.getElementById("email").value.trim();
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();

    // check if any of the fields are empty
    // !username = username is empty
    // !password = password is empty
    if (!firstName || !lastName || !email || !username || !password || !confirmPassword) {
        alert("Please fill in all fields.");
        return false; // prevent form submission
    }

    // check if the email is in a valid format (simple check for "@" and ".")
    if (!email.includes("@") || !email.includes(".")) {
        alert("Please enter a valid email address.");
        return false; // prevent form submission
    }

    // check if the username is between 3 and 20 characters
    if (username.length < 3 || username.length > 20) {
        alert("Username must be between 3 and 20 characters.");
        return false; // prevent form submission
    }

    // check if the password is at least 6 characters long
    if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false; // prevent form submission
    }

    // check if the password and confirm password fields match
    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false; // prevent form submission
    }

    // if all checks pass, return true to allow the form to be submitted
    return true;
}