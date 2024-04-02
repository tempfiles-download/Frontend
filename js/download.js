
document.getElementById('download-form').addEventListener('submit', async (e) => {
  e.preventDefault()
  const id = document.getElementById('id').value
  const password = document.getElementById('password').value
  window.location.replace(`${e.target.action}${id}/${password}`);
})
