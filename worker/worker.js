async function handleRequest(request) {
  const CONSOLE_URL = 'https://console.glynet.org';
  const url = new URL(request.url);
  const WORKER_ORIGIN = url.origin;

  const targetUrl = CONSOLE_URL + url.pathname + url.search;

  try {
    const response = await fetch(targetUrl, {
      method: request.method,
      headers: {
        'Accept': request.headers.get('Accept') || '*/*',
        'User-Agent': 'Mozilla/5.0',
      },
    });

    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('text/html')) {
      let body = await response.text();
      
      body = body.replace(
        new RegExp(CONSOLE_URL, 'g'),
        WORKER_ORIGIN
      );

      body = body.replace(
        '<head>',
        `<head><base href="${WORKER_ORIGIN}/">`
      );

      return new Response(body, {
        status: response.status,
        headers: {
          'content-type': contentType,
          'Access-Control-Allow-Origin': '*',
        },
      });
    }

    const body = await response.arrayBuffer();
    return new Response(body, {
      status: response.status,
      headers: {
        'content-type': contentType,
        'Access-Control-Allow-Origin': '*',
      },
    });

  } catch (error) {
    return new Response('Error: ' + error.message, {
      status: 500,
      headers: { 'content-type': 'text/plain' },
    });
  }
}

addEventListener('fetch', (event) => {
  event.respondWith(handleRequest(event.request));
});
