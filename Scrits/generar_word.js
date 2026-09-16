/**
 * generar_word.js — Genera el documento Word con las capturas ya tomadas
 */
const { Document, Packer, Paragraph, ImageRun, TextRun, AlignmentType } = require('docx');
const fs   = require('fs');
const path = require('path');

const outputDir = path.join(__dirname, 'capturas');
const docOutput = path.join(__dirname, 'Manual_Vistas_Sistema.docx');

const SECCIONES = [
    {
        titulo: '1. Vistas Públicas',
        vistas: [
            { slug: '01_index',  label: 'Página Principal (Index)' },
            { slug: '02_login',  label: 'Pantalla de Login' },
        ],
    },
    {
        titulo: '2. Módulo Administrador',
        vistas: [
            { slug: '03_admin_usuarios',    label: 'Gestión de Usuarios' },
            { slug: '04_admin_productos',   label: 'Gestión de Productos' },
            { slug: '05_admin_proveedores', label: 'Gestión de Proveedores' },
            { slug: '06_admin_reportes',    label: 'Reportes y Estadísticas' },
        ],
    },
    {
        titulo: '3. Módulo Cajero',
        vistas: [
            { slug: '07_cajero_ventas',       label: 'Nueva Venta' },
            { slug: '08_cajero_devoluciones', label: 'Devoluciones' },
            { slug: '09_cajero_recibo',       label: 'Recibo' },
            { slug: '10_cajero_historial',    label: 'Historial de Ventas' },
        ],
    },
    {
        titulo: '4. Módulo Bodeguero',
        vistas: [
            { slug: '11_bodeguero_inventario',  label: 'Inventario' },
            { slug: '12_bodeguero_entradas',    label: 'Entradas de Mercancía' },
            { slug: '13_bodeguero_ajustes',     label: 'Ajustes de Inventario' },
            { slug: '14_bodeguero_movimientos', label: 'Historial de Movimientos' },
            { slug: '15_bodeguero_reportes',    label: 'Reportes de Stock' },
        ],
    },
];

(async () => {
    console.log('📄 Generando documento Word...\n');
    const children = [];

    // ── Portada ───────────────────────────────────────────────
    children.push(
        new Paragraph({
            children: [new TextRun({ text: 'Manual de Vistas del Sistema', bold: true, size: 52, font: 'Calibri', color: '1a2d47' })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 240 },
        }),
        new Paragraph({
            children: [new TextRun({ text: 'Celeste Boutique', bold: true, size: 40, color: '8FB7C7', font: 'Calibri' })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 160 },
        }),
        new Paragraph({
            children: [new TextRun({ text: `Generado el ${new Date().toLocaleDateString('es-CO', { year:'numeric', month:'long', day:'numeric' })}`, size: 22, color: '888888', font: 'Calibri' })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 160 },
        }),
        new Paragraph({
            children: [new TextRun({ text: 'Sistema de gestión para punto de venta · PHP MVC', size: 20, color: 'aaaaaa', italics: true, font: 'Calibri' })],
            alignment: AlignmentType.CENTER,
            spacing: { after: 800 },
        }),
    );

    // ── Secciones ─────────────────────────────────────────────
    for (const seccion of SECCIONES) {
        // Título de sección — siempre en página nueva
        children.push(
            new Paragraph({
                children: [new TextRun({ text: seccion.titulo, bold: true, size: 36, color: '1a2d47', font: 'Calibri' })],
                pageBreakBefore: true,
                spacing: { after: 360 },
                border: { bottom: { color: '8FB7C7', size: 8, space: 6, style: 'single' } },
            }),
        );

        for (const vista of seccion.vistas) {
            const imgPath = path.join(outputDir, `${vista.slug}.png`);

            // Subtítulo de la vista
            children.push(
                new Paragraph({
                    children: [new TextRun({ text: vista.label, bold: true, size: 26, font: 'Calibri', color: '253E63' })],
                    spacing: { before: 280, after: 140 },
                }),
            );

            if (fs.existsSync(imgPath)) {
                const imgBuffer = fs.readFileSync(imgPath);
                children.push(
                    new Paragraph({
                        children: [
                            new ImageRun({
                                data:           imgBuffer,
                                transformation: { width: 620, height: 388 },
                                type:           'png',
                            }),
                        ],
                        alignment: AlignmentType.CENTER,
                        spacing: { after: 320 },
                    }),
                );
                console.log(`  ✅ ${vista.slug}.png — incluida`);
            } else {
                children.push(
                    new Paragraph({
                        children: [new TextRun({ text: `⚠ Imagen no encontrada: ${vista.slug}.png`, color: 'cc0000', italics: true, font: 'Calibri' })],
                        spacing: { after: 200 },
                    }),
                );
                console.log(`  ❌ ${vista.slug}.png — no encontrada`);
            }
        }
    }

    const doc    = new Document({ sections: [{ children }] });
    const buffer = await Packer.toBuffer(doc);
    fs.writeFileSync(docOutput, buffer);

    console.log(`\n✅ Documento generado: ${docOutput}`);
    console.log(`   Tamaño: ${(buffer.length / 1024 / 1024).toFixed(2)} MB\n`);
})();
