export default {

  async fetch(request, env) {
    const url = new URL(request.url);
    const {method} = request
    if (url.pathname.startsWith('/D')) {
      const params = url.pathname.split('/')
      switch (method) {
        case 'GET':
          return await download(params, env)
        case 'DELETE':
          return await remove(params)
        default:
          return new Response(JSON.stringify({
            error: 'Method unsupported or expected parameters not found.'
          }), {
            status: 400, headers: {'content-type': 'Application/JSON'}
          })
      }
    }
    if (method === 'POST') {
      return await upload(request, env)
    }
    return env.ASSETS.fetch(request);
  },
}

async function download(params, env) {
  const id = params[1];
  const password = params[2];
  const server = id.charAt(1); // Get server ID from 2nd letter of file ID
  const base = `https://${server}.tmpfil.es`;
  return await env.API.fetch(`${base}/${id}/${password}`);
}

async function upload(request, env) {
  const base = `https://1.tmpfil.es`;
  const init = {
    body: await request.formData(), method: 'POST'
  };
  try {
    const result = await env.API.fetch(base, init);
    if (result.status === 201) return result;
  } catch (e) {
    return new Request(e.message, {status: e.status})
  }
}

async function remove(params) {
  const id = params[1];
  const password = params[2];
  const server = id.charAt(1); // Get server ID from 2nd letter of file ID
  const base = `https://${server}.tmpfil.es`;
  return await fetch(`${base}/delete/?p=${password}`, {method: 'DELETE'});
}
