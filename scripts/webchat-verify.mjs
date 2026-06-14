// Headless verification of the embedded web chat (Converse + X-OAUTH2 token login).
// Logs in as the seeded dev user, opens /chat, and captures console + page errors +
// raw WebSocket frames so we can see exactly which SASL mechanism Converse negotiates
// and whether ejabberd returns <success/>.
//
//   node scripts/webchat-verify.mjs
import puppeteer from 'puppeteer';

const BASE = process.env.BASE || 'http://kewlchats.corp';
const EMAIL = process.env.EMAIL || 'andy@andyjames.org';
const PASSWORD = process.env.PASSWORD || 'password123';
const WAIT_MS = Number(process.env.WAIT_MS || 9000);

const out = [];
const log = (s) => out.push(s);

// Treat the HTTP origin as a secure context (so window.crypto.subtle exists), which is
// exactly what prod's HTTPS provides. Lets us verify the full flow without TLS in dev.
const browser = await puppeteer.launch({
  headless: true,
  userDataDir: '/tmp/kewlchats-pptr',
  args: [
    '--no-sandbox',
    '--disable-setuid-sandbox',
    `--unsafely-treat-insecure-origin-as-secure=${BASE}`,
  ],
});

try {
  const page = await browser.newPage();
  page.on('console', (m) => log(`[console.${m.type()}] ${m.text()}`));
  page.on('pageerror', (e) => log(`[pageerror] ${e.message}`));

  const cdp = await page.target().createCDPSession();
  await cdp.send('Network.enable');
  const onFrame = (dir) => ({ response }) => {
    const p = (response?.payloadData || '').toString().slice(0, 500);
    if (p.trim()) log(`[ws ${dir}] ${p}`);
  };
  cdp.on('Network.webSocketFrameSent', onFrame('=>'));
  cdp.on('Network.webSocketFrameReceived', onFrame('<='));

  // --- log in (Breeze) — skip if the persisted profile is already authenticated ---
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  if (await page.$('#email')) {
    await page.type('#email', EMAIL);
    await page.type('#password', PASSWORD);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
      page.keyboard.press('Enter'),
    ]);
    log(`[nav] after login -> ${page.url()}`);
  } else {
    log(`[nav] already authenticated -> ${page.url()}`);
  }

  // --- open web chat and let Converse attempt auth ---
  await page.goto(`${BASE}/chat`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, WAIT_MS));

  const ui = await page.evaluate(() => ({
    secureContext: window.isSecureContext,
    hasSubtle: !!(window.crypto && window.crypto.subtle),
    converseRoot: !!document.querySelector('converse-root'),
    conversejsChildren: document.querySelector('#conversejs')?.childElementCount || 0,
    chatUI: !!document.querySelector('converse-chats, .converse-chatboxes, converse-controlbox, converse-muc, converse-rosterview'),
  }));
  log(`[ui] ${JSON.stringify(ui)}`);
} finally {
  await browser.close();
}

const joined = out.join('\n');
const verdict = joined.includes('<success')
  ? 'AUTH SUCCESS ✓ (ejabberd returned <success/>)'
  : joined.includes('crypto.subtle')
    ? 'SCRAM attempted (crypto.subtle) — X-OAUTH2 not forced'
    : joined.includes('<failure')
      ? 'AUTH FAILURE (<failure/>)'
      : 'inconclusive';

console.log(joined);
console.log('\n=== VERDICT: ' + verdict + ' ===');
