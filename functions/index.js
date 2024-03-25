export async function onRequestPost({request, env}) {
  const base = `https://1.tmpfil.es`;
  const init = {
    method: 'POST',
    body: await request.formData(),
  }
  try {
    const result = await env.API.fetch(base, init);
    if (result.status === 201) return result;
    throw "Unexpected response from server."
  } catch (e) {
    return new Response(JSON.stringify({error: e.message}), {status: 500})
  }
}
