const form = document.getElementById('upload-form')

form.addEventListener("submit", async (e) => {
  e.preventDefault()
  fetch('https://1.tmpfil.es/', {
    method: 'POST',
    body: new FormData(form),
    cache: "no-cache"
  }).then(async (res) => {
      if (res.status === 201) {
        const data = await res.json()
        form.style.display = 'none'
        document.getElementById('title').innerHTML = 'File uploaded'
        document.getElementById('success').style.display = 'block'
        document.getElementById('url').value = data.url
        document.getElementById('server-password').value = data.password
        document.getElementById('deletion-password').value = data.deletepassword
      } else {
        if(res.ok)
          throw new Error((await res.json()).error)
        if(res.statusText !== "")
          throw new Error(res.statusText)
        throw new Error('Unable to upload file')
      }
    }
  ).catch(e => document.getElementById('failure').innerHTML = e.message)
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
