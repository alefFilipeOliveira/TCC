const form = document.getElementById('login-form');
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(form).entries());
  const resp = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  const out = await resp.json();
  if (!resp.ok) {
    alert(out.error || 'Falha no login');
    return;
  }
  localStorage.setItem('token', out.token);
  localStorage.setItem('user', JSON.stringify(out.user));
  window.location.href = '/app.html';
});