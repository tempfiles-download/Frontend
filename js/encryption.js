const buff_to_base64 = (buff) => btoa(new Uint8Array(buff).reduce((data, byte) => data + String.fromCharCode(byte), ''));

const getPasswordKey = (password) => crypto.subtle.importKey("raw", enc.encode(password), "PBKDF2", false, ["deriveKey",]);

const deriveKey = (passwordKey, salt, keyUsage) => crypto.subtle.deriveKey({
  name: "PBKDF2", salt: salt, iterations: 10001, hash: "SHA-256",
}, passwordKey, {name: "AES-GCM", length: 256}, false, keyUsage);

 async function encryptData(secretData, password) {
  try {
    const salt = crypto.getRandomValues(new Uint8Array(16));
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const passwordKey = await getPasswordKey(password);
    const aesKey = await deriveKey(passwordKey, salt, ["encrypt"]);
    const encryptedContent = await crypto.subtle.encrypt({
      name: "AES-GCM", iv: iv,
    }, aesKey, new TextEncoder().encode(secretData));

    const encryptedContentArr = new Uint8Array(encryptedContent);
    let buff = new Uint8Array(salt.byteLength + iv.byteLength + encryptedContentArr.byteLength);
    buff.set(salt, 0);
    buff.set(iv, salt.byteLength);
    buff.set(encryptedContentArr, salt.byteLength + iv.byteLength);
    return buff_to_base64(buff);;
  } catch (e) {
    console.error(e);
    return "";
  }
}

