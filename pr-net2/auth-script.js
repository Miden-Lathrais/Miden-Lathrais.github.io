// Toggle between login and register forms
document.getElementById('switch-to-register').addEventListener('click', () => {
    document.getElementById('login-form').classList.add('d-none');
    document.getElementById('register-form').classList.remove('d-none');
    document.getElementById('form-title').textContent = 'Register';
  });
  
  document.getElementById('switch-to-login').addEventListener('click', () => {
    document.getElementById('register-form').classList.add('d-none');
    document.getElementById('login-form').classList.remove('d-none');
    document.getElementById('form-title').textContent = 'Login';
  });
  