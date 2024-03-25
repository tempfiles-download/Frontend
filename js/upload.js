const form = document.getElementById('upload-form')

form.addEventListener("submit", async (e) => {
  e.preventDefault()
  const res = await fetch('/', {
    method: 'POST',
    body: new FormData(form),
    cache: "no-cache"
  })
  if (res.status === 201) {
    const data = await res.json()
    form.style.display = 'none'
    document.getElementById('title').innerHTML = 'File uploaded'
    document.getElementById('success').style.display = 'block'
    document.getElementById('url').value = data.url
    document.getElementById('server-password').value = data.password
    document.getElementById('deletion-password').value = data.deletepassword
  }else{
    console.error((await res.json()).error)
  }
})

const popup = document.getElementById('popup')
ondragover = ondragenter = (e) => {
  e.stopPropagation();
  e.preventDefault();
  popup.style.display = 'block'
};

ondrop = (e) => {
  e.stopPropagation();
  e.preventDefault();
  file.files = e.dataTransfer.files;
  file.dispatchEvent(new Event('change'));
  popup.style.display = 'none'
};
