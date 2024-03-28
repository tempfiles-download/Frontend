const form = document.getElementById('delete-form')
form.addEventListener('submit', async (e) => {
  e.preventDefault()
  const id = document.getElementById('id').value
  const password = document.getElementById('password').value
  fetch(`https://tmpfil.es/${id}/${password}`, {
    method: 'DELETE',
    cache: 'no-cache'
  }).then(async (res) => {
    const data = await res.json()
    if (res.status === 204) {
      form.style.display = 'none'
      document.getElementById('success').style.display = 'block'
    } else {
      if ('error' in data)
        throw new Error(data.error)
      if (res.statusText !== "")
        throw new Error(res.statusText)
      throw new Error('Unable to delete file')
    }
  }).catch(e => document.getElementById('failure').innerHTML = e.message)
})
