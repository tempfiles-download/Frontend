export async function onRequestGet(context) {
  const url = new URL(context.request.url);
  const params = url.pathname.split('/')
  const id = params[1];
  const password = params[2];
  const server = id.charAt(1); // Get server ID from 2nd letter of file ID
  const base = `https://${server}.tmpfil.es`;
  return await context.env.API.fetch(`${base}/${id}/${password}`);
}

export async function onRequestDelete(context){
  const url = new URL(context.request.url);
  const params = url.pathname.split('/')
  const id = params[1];
  const password = params[2];
  const server = id.charAt(1); // Get server ID from 2nd letter of file ID
  const base = `https://${server}.tmpfil.es`;
  return await context.env.API.fetch(`${base}/delete/?p=${password}`, {method: 'DELETE'});
}
