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

  if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('404 Not Found');
    return;
  }

  const ext = path.extname(filePath).toLowerCase();
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
