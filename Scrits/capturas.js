/**
 * capturas.js — Celeste Boutique
 * Usa UNA página por rol y navega entre vistas sin cerrarla,
 * así la sesión PHP se mantiene activa.
 */
const puppeteer = require('puppeteer');
const { Document, Packer, Paragraph, ImageRun, TextRun, AlignmentType } = require('docx');
const fs   = require('fs');
const path = require('path');

const CONFIG = {
    base:          'http://127.0.0.1:8081/sistema-boutique/sistema-boutique',
    adminEmail:    'Carlitos@gmail.com',
    adminPass:     'password',
    cajeroEmail:   'pipesito@gmail.com',
    cajeroPass:    'password',
    bodegueroEmail:'felipe@gmail.com',
    bodegueroPass: 'password',
    outputDir:     path.join(__dirname, 'capturas'),
    docOutput:     path.join(__dirname, 'Manual_Vistas_Sistema.docx'),
    W: 1440, H: 900,
};

const sleep = ms => new Promise(r => setTimeout(r, ms));
const log   = msg => console.log(`  → ${msg}`);

// ── Login en una página ya abierta ────────────────────────────
async function doLogin(page, email, pass) {
    await page.goto(`${CONFIG.base}/views/usuarios/login.php`,
        { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.type('input[name="email"]', email, { delay: 40 });
    await page.type('input[name="password"]', pass, { delay: 40 });
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 12000 });
    const url = page.url();
    if (url.includes('login')) throw new Error(`Login fallido, redirigió a: ${url}`);
    log(`Sesión activa — ${url}`);
}

// ── Capturar la página actual ─────────────────────────────────
async function capturar(page, url, slug, waitMs = 1200) {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await sleep(waitMs);
    // Cerrar SweetAlert si aparece
    try {
        const btn = await page.$('.swal2-confirm');
        if (btn) { await btn.click(); await sleep(300); }
    } catch (_) {}
    const file = path.join(CONFIG.outputDir, `${slug}.png`);
    await page.screenshot({ path: file });
    log(`Guardado: ${slug}.png`);
    return file;
}

(async () => {
    if (!fs.existsSync(CONFIG.outputDir)) fs.mkdirSync(CONFIG.outputDir, { recursive: true });
    console.log('\n🚀 Celeste Boutique — Capturas\n');

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
        defaultViewport: { width: CONFIG.W, height: CONFIG.H },
        protocolTimeout: 120000,
    });

    const results = [];

    // ── 1. Vistas públicas ────────────────────────────────────
    console.log('📸 Vistas públicas...');
    const pub = await browser.newPage();
    await pub.setViewport({ width: CONFIG.W, height: CONFIG.H });

    results.push({ label: 'Página Principal (Index)',
        file: await capturar(pub, `${CONFIG.base}/public/index.php`, '01_index'), rol: 'publico' });
    results.push({ label: 'Pantalla de Login',
        file: await capturar(pub, `${CONFIG.base}/views/usuarios/login.php`, '02_login'), rol: 'publico' });
    await pub.close();

    // ── 2. Administrador ──────────────────────────────────────
    console.log('\n📸 Módulo Administrador...');
    const adm = await browser.newPage();
    await adm.setViewport({ width: CONFIG.W, height: CONFIG.H });
    try {
        await doLogin(adm, CONFIG.adminEmail, CONFIG.adminPass);
        results.push({ label: 'Administrador — Gestión de Usuarios',
            file: await capturar(adm, `${CONFIG.base}/views/dashboard/admin.php`, '03_admin_usuarios'), rol: 'admin' });
        results.push({ label: 'Administrador — Gestión de Productos',
            file: await capturar(adm, `${CONFIG.base}/views/dashboard/productos.php`, '04_admin_productos'), rol: 'admin' });
        results.push({ label: 'Administrador — Gestión de Proveedores',
            file: await capturar(adm, `${CONFIG.base}/views/dashboard/proveedores.php`, '05_admin_proveedores'), rol: 'admin' });
        results.push({ label: 'Administrador — Reportes y Estadísticas',
            file: await capturar(adm, `${CONFIG.base}/views/dashboard/reportes.php`, '06_admin_reportes', 4000), rol: 'admin' });
    } catch (e) { log(`ERROR admin: ${e.message}`); }
    await adm.close();

    // ── 3. Cajero ─────────────────────────────────────────────
    console.log('\n📸 Módulo Cajero...');
    const caj = await browser.newPage();
    await caj.setViewport({ width: CONFIG.W, height: CONFIG.H });
    try {
        await doLogin(caj, CONFIG.cajeroEmail, CONFIG.cajeroPass);
        const tabs = [
            ['ventas',       '07_cajero_ventas',       'Cajero — Nueva Venta'],
            ['devoluciones', '08_cajero_devoluciones',  'Cajero — Devoluciones'],
            ['recibo',       '09_cajero_recibo',        'Cajero — Recibo'],
            ['historial',    '10_cajero_historial',     'Cajero — Historial de Ventas'],
        ];
        for (const [tab, slug, label] of tabs) {
            results.push({ label,
                file: await capturar(caj, `${CONFIG.base}/views/dashboard/cajero.php?tab=${tab}`, slug), rol: 'cajero' });
        }
    } catch (e) { log(`ERROR cajero: ${e.message}`); }
    await caj.close();

    // ── 4. Bodeguero ──────────────────────────────────────────
    console.log('\n📸 Módulo Bodeguero...');
    const bod = await browser.newPage();
    await bod.setViewport({ width: CONFIG.W, height: CONFIG.H });
    try {
        await doLogin(bod, CONFIG.bodegueroEmail, CONFIG.bodegueroPass);
        const tabs = [
            ['inventario',  '11_bodeguero_inventario',  'Bodeguero — Inventario'],
            ['entradas',    '12_bodeguero_entradas',     'Bodeguero — Entradas de Mercancía'],
            ['ajustes',     '13_bodeguero_ajustes',      'Bodeguero — Ajustes de Inventario'],
            ['movimientos', '14_bodeguero_movimientos',  'Bodeguero — Historial de Movimientos'],
            ['reportes',    '15_bodeguero_reportes',     'Bodeguero — Reportes de Stock'],
        ];
        for (const [tab, slug, label] of tabs) {
            results.push({ label,
                file: await capturar(bod, `${CONFIG.base}/views/dashboard/bodeguero.php?tab=${tab}`, slug), rol: 'bodeguero' });
        }
    } catch (e) { log(`ERROR bodeguero: ${e.message}`); }
    await bod.close();

    await browser.close();
    console.log('\n✅ Todas las capturas listas\n');

    // ── Generar Word ──────────────────────────────────────────
    console.log('📄 Generando Word...\n');

    const SECCIONES = [
        { titulo: '1. Vistas Públicas',        rols: ['publico']     },
        { titulo: '2. Módulo Administrador',    rols: ['admin']       },
        { titulo: '3. Módulo Cajero',           rols: ['cajero']      },
        { titulo: '4. Módulo Bodeguero',        rols: ['bodeguero']   },
    ];

    const children = [
        new Paragraph({
            children: [new TextRun({ text: 'Manual de Vistas del Sistema', bold: true, size: 52, font: 'Calibri', color: '1a2d47' })],
            alignment: AlignmentType.CENTER, spacing: { after: 240 },
        }),
        new Paragraph({
            children: [new TextRun({ text: 'Celeste Boutique', bold: true, size: 40, color: '8FB7C7', font: 'Calibri' })],
            alignment: AlignmentType.CENTER, spacing: { after: 160 },
        }),
        new Paragraph({
            children: [new TextRun({ text: `Generado el ${new Date().toLocaleDateString('es-CO', { year:'numeric', month:'long', day:'numeric' })}`, size: 22, color: '888888', font: 'Calibri' })],
            alignment: AlignmentType.CENTER, spacing: { after: 800 },
        }),
    ];

    for (const sec of SECCIONES) {
        children.push(new Paragraph({
            children: [new TextRun({ text: sec.titulo, bold: true, size: 36, color: '1a2d47', font: 'Calibri' })],
            pageBreakBefore: true, spacing: { after: 360 },
            border: { bottom: { color: '8FB7C7', size: 8, space: 6, style: 'single' } },
        }));

        for (const r of results.filter(r => sec.rols.includes(r.rol))) {
            children.push(new Paragraph({
                children: [new TextRun({ text: r.label, bold: true, size: 26, color: '253E63', font: 'Calibri' })],
                spacing: { before: 280, after: 140 },
            }));
            if (r.file && fs.existsSync(r.file)) {
                children.push(new Paragraph({
                    children: [new ImageRun({ data: fs.readFileSync(r.file), transformation: { width: 620, height: 388 }, type: 'png' })],
                    alignment: AlignmentType.CENTER, spacing: { after: 320 },
                }));
                console.log(`  ✅ ${path.basename(r.file)}`);
            } else {
                children.push(new Paragraph({
                    children: [new TextRun({ text: '⚠ Captura no disponible', color: 'cc0000', italics: true })],
                    spacing: { after: 200 },
                }));
                console.log(`  ❌ ${r.label} — sin captura`);
            }
        }
    }

    const buf = await Packer.toBuffer(new Document({ sections: [{ children }] }));
    fs.writeFileSync(CONFIG.docOutput, buf);
    console.log(`\n✅ Word generado: ${CONFIG.docOutput}`);
    console.log(`   Tamaño: ${(buf.length/1024/1024).toFixed(2)} MB\n🎉 Listo!\n`);
})();
