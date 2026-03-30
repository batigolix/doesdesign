// AI generated
import { chromium } from 'playwright';
import pixelmatch from 'pixelmatch';
import { PNG } from 'pngjs';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const D7_BASE = 'https://doesdesign.nl';
const D11_BASE = 'https://doesdesign.ddev.site';
const OUTPUT_DIR = path.join(__dirname, 'visual-regression');
const VIEWPORT = { width: 1280, height: 900 };

interface PageDef {
  name: string;
  d7: string;
  d11: string;
}

interface PageResult {
  name: string;
  d7: string;
  d11: string;
  error?: string;
  mismatchPct?: number;
  mismatchedPixels?: number;
  width?: number;
  height?: number;
}

const pages: PageDef[] = [
  { name: '00-home', d7: '/pagina/homepage', d11: '/' },
  { name: '01-gesp-texmex', d7: '/sieraad/gesp-texmex', d11: '/sieraad/gesp-texmex' },
  { name: '02-heren-ringen', d7: '/soort-sieraden/heren-ringen-0', d11: '/soort-sieraden/heren-ringen' },
  { name: '03-dames-ringen', d7: '/soort-sieraden/dames-ringen', d11: '/soort-sieraden/dames-ringen' },
  { name: '04-trouwringen', d7: '/soort-sieraden/unieke-en-originele-trouwringen', d11: '/soort-sieraden/unieke-trouwringen' },
  { name: '05-colliers', d7: '/soort-sieraden/bijzondere-en-originele-colliers-hangers-broches-van-mokume-gane', d11: '/soort-sieraden/colliers-hangers' },
  { name: '06-about', d7: '/about', d11: '/birgit-doesborg-goudsmid-en-zilversmid-hertogenbosch' },
  { name: '07-contact', d7: '/contact', d11: '/contact' },
  { name: '08-object-detail', d7: '/sieraad/kardinaalring-franciscus-mokume-gane-en-carneool', d11: '/sieraad/kardinaalring-franciscus-met-mokume-gane-en-carneool' },
  { name: '09-mokume-gane', d7: '/pagina/doesdesign-mokume-gane-sieraden', d11: '/over-mokume-gane-mumujin' },
  { name: '10-nieuws-1', d7: '/nieuws/expositie-bossche-wintersalon-2025-2026', d11: '/nieuws/expositie-bossche-wintersalon-2025-2026' },
  { name: '11-nieuws-2', d7: '/nieuws/expositie-sieraden-en-objecten-9-juli-2023', d11: '/nieuws/expositie-sieraden-en-objecten-9-juli-2023' },
  { name: '12-trouwringen-page', d7: '/pagina/originele-en-unieke-trouwringen-van-mokume-gane', d11: '/unieke-en-bijzondere-trouwringen' },
];

function readPng(filePath: string): Promise<PNG> {
  return new Promise((resolve, reject) => {
    const stream = fs.createReadStream(filePath).pipe(new PNG());
    stream.on('parsed', function (this: PNG) { resolve(this); });
    stream.on('error', reject);
  });
}

function createResizedPng(img: PNG, width: number, height: number): PNG {
  const resized = new PNG({ width, height });
  for (let i = 0; i < resized.data.length; i += 4) {
    resized.data[i] = 255;
    resized.data[i + 1] = 255;
    resized.data[i + 2] = 255;
    resized.data[i + 3] = 255;
  }
  for (let y = 0; y < img.height && y < height; y++) {
    for (let x = 0; x < img.width && x < width; x++) {
      const srcIdx = (y * img.width + x) * 4;
      const dstIdx = (y * width + x) * 4;
      resized.data[dstIdx] = img.data[srcIdx];
      resized.data[dstIdx + 1] = img.data[srcIdx + 1];
      resized.data[dstIdx + 2] = img.data[srcIdx + 2];
      resized.data[dstIdx + 3] = img.data[srcIdx + 3];
    }
  }
  return resized;
}

(async () => {
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  }

  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: VIEWPORT });
  const results: PageResult[] = [];

  console.log(`Visual regression: comparing ${pages.length} pages\n`);

  for (const pg of pages) {
    const d7Path = path.join(OUTPUT_DIR, `${pg.name}-d7.png`);
    const d11Path = path.join(OUTPUT_DIR, `${pg.name}-d11.png`);
    const diffPath = path.join(OUTPUT_DIR, `${pg.name}-diff.png`);

    const pageD7 = await context.newPage();
    const pageD11 = await context.newPage();

    try {
      await Promise.all([
        pageD7.goto(`${D7_BASE}${pg.d7}`, { waitUntil: 'networkidle', timeout: 15000 }),
        pageD11.goto(`${D11_BASE}${pg.d11}`, { waitUntil: 'networkidle', timeout: 15000 }),
      ]);

      // Wait for async content to finish loading (Flickr JSONP, Google
      // Translate widget, Splide slider initialization). Each selector
      // is optional — we catch timeouts silently so pages without these
      // elements don't block the run.
      const waitForAsync = (page: typeof pageD7) =>
        Promise.allSettled([
          page.waitForSelector('#flickr_images ul', { timeout: 5000 }),
          page.waitForSelector('.goog-te-gadget', { timeout: 5000 }),
          page.waitForSelector('.splide.is-initialized', { timeout: 5000 }),
        ]);
      await Promise.all([waitForAsync(pageD7), waitForAsync(pageD11)]);

      // Hide the header so only content is compared.
      const hideHeader = (page: typeof pageD7) =>
        page.evaluate(() => {
          const el = document.querySelector('#content-top') as HTMLElement;
          if (el) el.style.display = 'none';
        });
      await Promise.all([hideHeader(pageD7), hideHeader(pageD11)]);

      await Promise.all([
        pageD7.screenshot({ path: d7Path, fullPage: true }),
        pageD11.screenshot({ path: d11Path, fullPage: true }),
      ]);
    } catch (err) {
      const msg = err instanceof Error ? err.message : String(err);
      console.log(`  ⚠ ${pg.name}: ${msg}`);
      results.push({ name: pg.name, d7: pg.d7, d11: pg.d11, error: msg });
      await pageD7.close();
      await pageD11.close();
      continue;
    }

    await pageD7.close();
    await pageD11.close();

    let imgD7 = await readPng(d7Path);
    let imgD11 = await readPng(d11Path);

    const w = Math.max(imgD7.width, imgD11.width);
    const h = Math.max(imgD7.height, imgD11.height);

    if (imgD7.width !== w || imgD7.height !== h) {
      imgD7 = createResizedPng(imgD7, w, h);
    }
    if (imgD11.width !== w || imgD11.height !== h) {
      imgD11 = createResizedPng(imgD11, w, h);
    }

    const diff = new PNG({ width: w, height: h });
    const mismatchedPixels = pixelmatch(
      imgD7.data, imgD11.data, diff.data, w, h,
      { threshold: 0.3 }
    );
    const totalPixels = w * h;
    const mismatchPct = parseFloat(((mismatchedPixels / totalPixels) * 100).toFixed(1));

    fs.writeFileSync(diffPath, PNG.sync.write(diff));

    const status = mismatchPct > 5 ? '✗' : '✓';
    console.log(`  ${status} ${pg.name}: ${mismatchPct}% diff (${mismatchedPixels.toLocaleString()} px)`);

    results.push({
      name: pg.name,
      d7: pg.d7,
      d11: pg.d11,
      mismatchPct,
      mismatchedPixels,
      width: w,
      height: h,
    });
  }

  await browser.close();

  const html = `<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<title>Visual Regression: D7 vs D11</title>
<style>
  body { font-family: system-ui, sans-serif; margin: 2rem; background: #f5f5f5; }
  h1 { color: #333; }
  .summary { margin-bottom: 2rem; }
  .summary td { padding: 4px 12px; }
  .pass { color: #2a7d2a; }
  .fail { color: #c00; font-weight: bold; }
  .page-compare { margin-bottom: 3rem; border: 1px solid #ddd; background: #fff; padding: 1rem; border-radius: 8px; }
  .page-compare h2 { margin-top: 0; }
  .images { display: flex; gap: 1rem; overflow-x: auto; }
  .images figure { margin: 0; flex: 0 0 auto; }
  .images figcaption { font-weight: bold; margin-bottom: 0.5rem; text-align: center; }
  .images img { max-width: 400px; border: 1px solid #ccc; }
</style>
</head>
<body>
<h1>Visual Regression: D7 vs D11</h1>
<p>Generated: ${new Date().toLocaleString('nl-NL')}</p>

<table class="summary">
<tr><th>Page</th><th>Diff %</th><th>Status</th></tr>
${results.map(r => {
  if (r.error) return `<tr><td>${r.name}</td><td colspan="2" class="fail">Error: ${r.error}</td></tr>`;
  const cls = r.mismatchPct! > 5 ? 'fail' : 'pass';
  const st = r.mismatchPct! > 5 ? 'DIFFERS' : 'OK';
  return `<tr><td>${r.name}</td><td class="${cls}">${r.mismatchPct}%</td><td class="${cls}">${st}</td></tr>`;
}).join('\n')}
</table>

${results.filter(r => !r.error).map(r => `
<div class="page-compare">
  <h2>${r.name} <small>(${r.mismatchPct}% diff)</small></h2>
  <p>D7: <code>${r.d7}</code> &nbsp;|&nbsp; D11: <code>${r.d11}</code></p>
  <div class="images">
    <figure>
      <figcaption>D7</figcaption>
      <img src="${r.name}-d7.png" alt="D7">
    </figure>
    <figure>
      <figcaption>D11</figcaption>
      <img src="${r.name}-d11.png" alt="D11">
    </figure>
    <figure>
      <figcaption>Diff</figcaption>
      <img src="${r.name}-diff.png" alt="Diff">
    </figure>
  </div>
</div>
`).join('\n')}

</body>
</html>`;

  const reportPath = path.join(OUTPUT_DIR, 'report.html');
  fs.writeFileSync(reportPath, html);
  console.log(`\nReport: ${reportPath}`);
})();
