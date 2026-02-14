// runs when the Login button is clicked
function validateLoginForm() {

    // get the username and password from the form
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    // check if the username or password is empty
    // !username = username is empty
    // !password = password is empty
    if (!username || !password) {
        alert('Please fill in all fields.');
        return false; // prevent form submission
    }

    // check if the username is between 3 and 20 characters
    if (username.length < 3 || username.length > 20) {
        alert('Username must be between 3 and 20 characters.');
        return false; // prevent form submission
    }

    // check if the password is at least 6 characters long
    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false; // prevent form submission
    }   

    // if all checks pass, return true to allow the form to be submitted
    return true;
}   