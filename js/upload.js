const form = document.getElementById('upload-form')
let file = null
form.addEventListener("submit", async (e) => {
  e.preventDefault()
  fetch('https://1.tmpfil.es/', {
    method: 'POST',
    body: new FormData(form),
    cache: "no-cache"
  }).then(async (res) => {
    const data = await res.json()
    if (res.status === 201) {
        form.style.display = 'none'
        document.getElementById('title').innerHTML = 'File uploaded'
        document.getElementById('success').style.display = 'block'
        document.getElementById('url').value = data.url
        document.getElementById('server-password').value = data.password
        document.getElementById('deletion-password').value = data.deletepassword
      } else {
      if ('error' in data)
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

window.onloadTurnstileCallback = () => {
  turnstile.render('#captcha', {sitekey: '0x4AAAAAAAV3ZLwfyLkohb7z'});
};

document.addEventListener("DOMContentLoaded", () => {
if ("serviceWorker" in navigator) navigator.serviceWorker.register("/sw.js");
})
