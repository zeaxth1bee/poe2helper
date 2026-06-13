// Cloudflare Worker — POST https://poe2helper.zeaxth1bee.workers.dev
export default {
  async fetch(request) {
    if (request.method === 'OPTIONS') return new Response(null, { headers: { 'Access-Control-Allow-Origin': '*', 'Access-Control-Allow-Methods': 'POST' } });
    if (request.method !== 'POST') return Response.json({ error: 'POST only' }, { status: 405 });
    try {
      const data = await request.json();
      return Response.json({ status: 'ok', ts: Date.now() });
    } catch { return Response.json({ error: 'bad json' }, { status: 400 }); }
  }
}
