// Function to validate the registration form
function validateForm() {
  // Get input values
  const fname = document.getElementById('firstName').value.trim();
  const lname = document.getElementById('lastName').value.trim();
  const dob = document.getElementById('dob').value;
  const usertype = document.getElementById('usertype').value;
  const address = document.getElementById('address').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  const terms = document.getElementById('terms').checked;

  // Reset styles
  document.querySelectorAll('input, select').forEach(input => input.style.borderColor = '');
  document.querySelectorAll('label').forEach(label => label.style.color = '#000');

  // Regex
  const nameRegex = /^[a-zA-Z.\-\s]+$/;
  const emailRegex = /^[a-z0-9._-]+@(gmail|hotmail|yahoo)\.com$/;
  const phoneRegex = /^\d{10,15}$/;  

  // Password checks
  const passwordLength = password.length >= 8;
  const hasLower = /[a-z]/.test(password);
  const hasUpper = /[A-Z]/.test(password);
  const hasDigit = /\d/.test(password);
  const hasSpecial = /[^A-Za-z0-9]/.test(password);

  // Age calculation
  const dobDate = new Date(dob);
  const today = new Date();
  let age = today.getFullYear() - dobDate.getFullYear();
  const m = today.getMonth() - dobDate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < dobDate.getDate())) age--;

  // Collect errors
  const errors = [];

  if (!nameRegex.test(fname) || fname.length < 2) {
    errors.push("First name must be valid and at least 2 characters.");
    document.getElementById('firstName').style.borderColor = 'red';
  }

  if (!nameRegex.test(lname) || lname.length < 2) {
    errors.push("Last name must be valid and at least 2 characters.");
    document.getElementById('lastName').style.borderColor = 'red';
  }

  if (!emailRegex.test(email)) {
    errors.push("Email must be valid and end with gmail, hotmail, or yahoo.");
    document.getElementById('email').style.borderColor = 'red';
  }

  if (!phoneRegex.test(phone)) {
    errors.push("Phone number must be 10 to 15 digits long and contain only numbers.");
    document.getElementById('phone').style.borderColor = 'red';
  }

  if (address.length < 5) {
    errors.push("Address must be at least 5 characters.");
    document.getElementById('address').style.borderColor = 'red';
  }

  if (!passwordLength || !hasLower || !hasUpper || !hasDigit || !hasSpecial) {
    errors.push("Password must be at least 8 characters with uppercase, lowercase, number, and special character.");
    document.getElementById('password').style.borderColor = 'red';
  }

  if (password !== confirmPassword) {
    errors.push("Passwords do not match.");
    document.getElementById('confirmPassword').style.borderColor = 'red';
  }

  if (!dob || age < 18) {
    errors.push("You must be at least 18 years old.");
    document.getElementById('dob').style.borderColor = 'red';
  }

  if (usertype === "") {
    errors.push("Please select a user type.");
    document.getElementById('usertype').style.borderColor = 'red';
  }

  if (!terms) {
    errors.push("You must accept the terms and conditions.");
    document.getElementById('terms').style.outline = '2px solid red';
  } else {
    document.getElementById('terms').style.outline = '';
  }

  // errors alert 
  if (errors.length > 0) {
    alert("Please fix the following errors:\n\n" + errors.join("\n"));
    return false;
  }

  return true; 
}

function validateLoginForm()
{
    const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();

      if (!email || !password) {
        alert('Please fill in all fields');
        return false;
      }

      const emailRegex = /^[\w.-]+@(gmail|hotmail|yahoo)\.com$/;
      if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');        
        return false;
      }

      if (password.length < 8) {
        alert('Password must be at least 8 characters');        
        return false;
      }
        return true; 

}


