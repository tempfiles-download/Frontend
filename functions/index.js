export async function onRequestPost(context) {
  const base = `https://1.tmpfil.es`;
  const init = {
    body: await context.request.formData(), method: 'POST'
  };
  try {
    const result = await context.env.API.fetch(base, init);
    if (result.status === 201) return result;
    return new Response(JSON.stringify({error: "No host available"}), {status: 502})
  } catch (e) {
    return new Request(e.message, {status: e.status})
  }
}
