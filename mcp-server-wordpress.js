#!/usr/bin/env node
const https = require('https');
const fs = require('fs');
const path = require('path');

const WP_URL = 'lienthongdaihoc.com';
const PASS_FILE = path.join(__dirname, '.wp-mcp-pass');

function getAuth() {
  const pass = fs.readFileSync(PASS_FILE, 'utf8').trim();
  return Buffer.from('admin:' + pass).toString('base64');
}

function wpApi(method, endpoint, body) {
  return new Promise((resolve, reject) => {
    const opts = {
      hostname: WP_URL,
      path: '/wp-json/wp/v2/' + endpoint,
      method,
      headers: {
        'Authorization': 'Basic ' + getAuth(),
        'Content-Type': 'application/json',
      },
    };
    const req = https.request(opts, (res) => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => {
        try { resolve({ status: res.statusCode, data: JSON.parse(data) }); }
        catch { resolve({ status: res.statusCode, data }); }
      });
    });
    req.on('error', reject);
    if (body) req.write(JSON.stringify(body));
    req.end();
  });
}

const rl = require('readline').createInterface({ input: process.stdin });
let buf = '';
rl.on('line', (line) => {
  buf += line;
  try {
    const msg = JSON.parse(buf);
    buf = '';
    handle(msg).then(resp => {
      process.stdout.write(JSON.stringify(resp) + '\n');
    }).catch(err => {
      process.stdout.write(JSON.stringify({
        jsonrpc: '2.0', id: msg.id || null,
        error: { code: -32603, message: err.message }
      }) + '\n');
    });
  } catch { buf += '\n'; }
});

async function handle(msg) {
  const { id, method, params } = msg;

  if (method === 'initialize') {
    return {
      jsonrpc: '2.0', id,
      result: {
        protocolVersion: '2024-11-05',
        capabilities: { tools: {} },
        serverInfo: { name: 'WordPress MCP', version: '1.0.0' },
      },
    };
  }

  if (method === 'tools/list') {
    return {
      jsonrpc: '2.0', id,
      result: {
        tools: [
          {
            name: 'create-post',
            description: 'Tạo bài viết WordPress mới',
            inputSchema: {
              type: 'object',
              properties: {
                title: { type: 'string', description: 'Tiêu đề bài viết' },
                content: { type: 'string', description: 'Nội dung bài viết (HTML)' },
                status: { type: 'string', enum: ['publish', 'draft', 'pending'], default: 'publish' },
                categories: { type: 'array', items: { type: 'number' }, description: 'ID danh mục' },
              },
              required: ['title'],
            },
          },
          {
            name: 'list-posts',
            description: 'Danh sách bài viết gần đây',
            inputSchema: {
              type: 'object',
              properties: {
                per_page: { type: 'number', default: 10 },
                status: { type: 'string', enum: ['publish', 'draft', 'any'], default: 'publish' },
              },
            },
          },
          {
            name: 'delete-post',
            description: 'Xóa bài viết',
            inputSchema: {
              type: 'object',
              properties: {
                id: { type: 'number', description: 'ID bài viết' },
              },
              required: ['id'],
            },
          },
        ],
      },
    };
  }

  if (method === 'tools/call') {
    const { name, arguments: args } = params;
    let result;

    if (name === 'create-post') {
      const r = await wpApi('POST', 'posts', {
        title: args.title,
        content: args.content || '',
        status: args.status || 'publish',
        categories: args.categories || [],
      });
      if (r.status >= 400) throw new Error(r.data.message || 'Lỗi tạo post');
      result = { content: [{ type: 'text', text: JSON.stringify({
        id: r.data.id, link: r.data.link, title: r.data.title?.rendered, status: r.data.status,
      }, null, 2) }] };
    } else if (name === 'list-posts') {
      const r = await wpApi('GET',
        `posts?per_page=${args.per_page || 10}&status=${args.status || 'publish'}&_fields=id,title,status,date,link`);
      if (r.status >= 400) throw new Error(r.data.message || 'Lỗi lấy danh sách');
      result = { content: [{ type: 'text', text: JSON.stringify(r.data, null, 2) }] };
    } else if (name === 'delete-post') {
      const r = await wpApi('DELETE', `posts/${args.id}?force=true`);
      if (r.status >= 400) throw new Error(r.data.message || 'Lỗi xóa post');
      result = { content: [{ type: 'text', text: JSON.stringify({ deleted: true, id: args.id }, null, 2) }] };
    } else {
      throw new Error('Tool not found: ' + name);
    }

    return { jsonrpc: '2.0', id, result };
  }

  return { jsonrpc: '2.0', id, error: { code: -32601, message: 'Method not found' } };
}
