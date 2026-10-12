const http = require('http');
const fs = require('fs');
const path = require('path');
const zlib = require('zlib');

const mimeTypes = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml',
  '.mp4': 'video/mp4',
  '.pdf': 'application/pdf'
};

const compressibleTypes = new Set(['.html', '.css', '.js', '.json', '.svg']);

const server = http.createServer((req, res) => {
  let reqPath = req.url.split('?')[0];
  if (reqPath === '/') reqPath = '/index.html';
  const filePath = path.join(__dirname, decodeURIComponent(reqPath));

  const ext = path.extname(filePath).toLowerCase();

  // Si se solicita un archivo PHP a través del servidor Node.js de preview:
  if (ext === '.php' || reqPath.startsWith('/club')) {
    if (ext === '.php' || reqPath.endsWith('/club') || reqPath.endsWith('/club/')) {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Grobe Club - Entorno PHP</title>
          <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;700;800&family=Lora:wght@600&display=swap" rel="stylesheet">
          <style>
            body { background: #0b0c0e; color: #fff; font-family: 'Nunito Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; text-align: center; }
            .box { background: rgba(22, 25, 32, 0.95); border: 1px solid rgba(210, 31, 60, 0.4); border-radius: 24px; padding: 40px; max-width: 520px; box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 30px rgba(210, 31, 60, 0.15); }
            h2 { font-family: 'Lora', serif; font-size: 1.8rem; margin: 12px 0 8px; }
            p { color: #94a3b8; font-size: 14px; line-height: 1.6; margin: 16px 0; }
            .badge { background: rgba(210, 31, 60, 0.15); color: #FF7B90; border: 1px solid rgba(210, 31, 60, 0.3); padding: 4px 12px; border-radius: 99px; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
            .notice { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px; font-size: 13px; color: #cbd5e1; text-align: left; margin: 16px 0 24px; }
            .btn { display: inline-block; background: linear-gradient(135deg, #D21F3C 0%, #A81930 100%); color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 13px; }
          </style>
        </head>
        <body>
          <div class="box">
            <span class="badge">Módulo Backend PHP</span>
            <h2>Grobe Club Activo</h2>
            <p>Este servidor local Node.js (puerto 3333) sirve la web visual estática con compresión Gzip.</p>
            <div class="notice">
              Para ejecutar el sistema dinámico de autenticación y base de datos:
              <br><br>
              <strong>1.</strong> Inicia <strong>Apache</strong> y <strong>MySQL</strong> en XAMPP.<br>
              <strong>2.</strong> Accede a: <code>http://localhost/johns-grobe-fullweb/club/</code>
            </div>
            <a href="/index.html" class="btn">← Volver a la Web Principal</a>
          </div>
        </body>
        </html>
      );
      return;
    }
  }

  if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('404 Not Found');
    return;
  }

  const contentType = mimeTypes[ext] || 'application/octet-stream';
  const acceptEncoding = req.headers['accept-encoding'] || '';

  const headers = {
    'Content-Type': contentType,
    'Cache-Control': (ext === '.webp' || ext === '.png' || ext === '.jpg' || ext === '.svg')
      ? 'public, max-age=86400' 
      : 'no-cache'
  };

  const rawStream = fs.createReadStream(filePath);

  if (compressibleTypes.has(ext)) {
    if (acceptEncoding.includes('gzip')) {
      headers['Content-Encoding'] = 'gzip';
      res.writeHead(200, headers);
      rawStream.pipe(zlib.createGzip({ level: 6 })).pipe(res);
      return;
    } else if (acceptEncoding.includes('deflate')) {
      headers['Content-Encoding'] = 'deflate';
      res.writeHead(200, headers);
      rawStream.pipe(zlib.createDeflate()).pipe(res);
      return;
    }
  }

  res.writeHead(200, headers);
  rawStream.pipe(res);
});

server.listen(3333, () => {
  console.log('Preview server running on http://localhost:3333 with Gzip compression');
});
