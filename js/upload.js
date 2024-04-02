const form = document.getElementById('upload-form')
form.addEventListener('submit', async (e) => {
  e.preventDefault()
  const formData = new FormData(form);
  const file = formData.get('file')

  // Modify Form Data
  const encodedContent = btoa(String.fromCharCode(...new Uint8Array(await file.arrayBuffer())));
  const password = formData.get('password') || generatePassword(6,20)
  const data = await encryptData(JSON.stringify({
    name: file.name,
    type: file.type,
    size: file.size,
    data: encodedContent
  }), password)
  formData.set('file', data);

  // Post formData to upload URL
  fetch(e.target.action, {
    method: 'POST',
    body: formData,
    cache: 'no-cache'
  }).then(async (res) => {
    let data = null
    try {
      data = await res.json()
    }catch (e){}
    if (res.status === 201) {
        form.style.display = 'none'
        document.getElementById('title').innerHTML = 'File uploaded'
        document.getElementById('success').style.display = 'block'
        document.getElementById('url').value = `${data.url}${password}`
        document.getElementById('deletion-password').value = data.deletepassword
      } else {
      if (data !== null && 'error' in data)
          throw new Error(data.error)
        if(res.statusText !== '')
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
  turnstile.render('#captcha', {sitekey: '0x4AAAAAAAV3ZLwfyLkohb7z', 
    callback: () => {
    document.getElementById('submit').disabled = false
    },
  'error-callback': (e) => {
    document.getElementById('submit').disabled = true
    document.getElementById('failure').innerText = `Unable to verify browser. (Error ${e})`
  }});
}

function generatePassword(minLength, maxLength) {
  const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  const length = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
  let result = '';
  for (let i = 0; i < length; i++) {
    const randomIndex = Math.floor(Math.random() * characters.length);
    result += characters.charAt(randomIndex);
  }
  return result;
}
