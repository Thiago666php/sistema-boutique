<?php
/**
 * Partial: recibo_template.php
 * Variables esperadas: $recibo (array), $reciboDetalle (array)
 * Diseño: ticket de papel estilo cash receipt
 */

// Número de referencia basado en el id de la venta
$refNum = '#' . str_pad($recibo['id_venta'], 6, '0', STR_PAD_LEFT)
        . date('dmY', strtotime($recibo['created_at'])) . '#';

// Generar barras del código de barras SVG a partir del id_venta
$barcode = '';
$seed    = intval($recibo['id_venta']) * 7 + 13;
srand($seed);
$x = 0;
for ($i = 0; $i < 60; $i++) {
    $w = rand(1, 3);
    if ($i % 2 === 0) {
        $barcode .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="50" fill="#111"/>';
    }
    $x += $w + 1;
}
$barcodeWidth = $x;
?>

<style>
/* ── Reset de impresión ───────────────────────────────────── */
@media print {
    body * { visibility: hidden !important; }
    #ticket-recibo,
    #ticket-recibo * { visibility: visible !important; }
    #ticket-recibo {
        position: fixed !important;
        top: 0 !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 340px !important;
        margin: 0 !important;
        box-shadow: none !important;
    }
    .no-print { display: none !important; }
}

/* ── Ticket ───────────────────────────────────────────────── */
#ticket-recibo {
    font-family: 'Arial', sans-serif;
    width: 340px;
    margin: 0 auto;
    background: #fff;
    color: #111;
    padding: 0;
    /* Borde dentado superior */
    --tooth: 10px;
    border-left:  1px solid #ddd;
    border-right: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    position: relative;
}

/* Efecto dentado arriba */
#ticket-recibo::before {
    content: '';
    display: block;
    height: 12px;
    background:
        radial-gradient(circle at 50% 0%, #fff 6px, transparent 6px),
        linear-gradient(#ddd 1px, transparent 1px);
    background-size: 14px 12px, 100% 1px;
    background-position: 0 0, 0 0;
    border-top: 1px solid #ddd;
}

/* Efecto dentado abajo */
#ticket-recibo::after {
    content: '';
    display: block;
    height: 12px;
    background:
        radial-gradient(circle at 50% 100%, #fff 6px, transparent 6px),
        linear-gradient(transparent calc(100% - 1px), #ddd calc(100% - 1px));
    background-size: 14px 12px, 100% 1px;
    background-position: 0 0, 0 0;
}

.ticket-body {
    padding: 18px 22px 14px;
}

/* Título */
.ticket-title {
    text-align: center;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 14px;
    color: #111;
}

/* Separador */
.ticket-sep {
    border: none;
    border-top: 1px solid #bbb;
    margin: 10px 0;
}
.ticket-sep.dashed {
    border-top: 1px dashed #bbb;
}

/* Info header (dos columnas) */
.ticket-info {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 2px 12px;
    font-size: 12px;
    margin-bottom: 2px;
}
.ticket-info .lbl { color: #444; font-weight: 400; }
.ticket-info .val { text-align: right; color: #111; font-weight: 400; }

/* Tabla de ítems */
.ticket-items {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin: 8px 0;
}
.ticket-items thead tr th {
    font-weight: 700;
    padding: 4px 0;
    border-bottom: 1px solid #bbb;
    text-align: left;
}
.ticket-items thead tr th:last-child { text-align: right; }
.ticket-items tbody tr td {
    padding: 4px 0;
    vertical-align: top;
    color: #222;
}
.ticket-items tbody tr td:last-child {
    text-align: right;
    white-space: nowrap;
}
.ticket-items tbody tr td .item-qty {
    font-size: 11px;
    color: #666;
}

/* Totales */
.ticket-totals { font-size: 13px; margin: 6px 0; }
.ticket-totals .row {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
    color: #444;
}
.ticket-totals .row.total {
    font-size: 16px;
    font-weight: 800;
    color: #111;
    margin-top: 4px;
}

/* Estado anulada */
.ticket-anulada {
    text-align: center;
    background: #fde8e8;
    color: #c0392b;
    font-weight: 700;
    font-size: 12px;
    padding: 5px;
    border-radius: 4px;
    margin: 6px 0;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Código de barras */
.ticket-barcode {
    text-align: center;
    margin: 10px 0 4px;
}
.ticket-barcode svg {
    display: block;
    margin: 0 auto;
}
.ticket-barcode .ref {
    font-size: 11px;
    letter-spacing: 2px;
    color: #333;
    margin-top: 4px;
}

/* Pie */
.ticket-footer {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: #111;
    padding: 8px 0 2px;
    letter-spacing: 0.5px;
}
</style>

<div id="ticket-recibo">
    <div class="ticket-body">

        <!-- Título -->
        <p class="ticket-title">Recibo de Venta</p>

        <hr class="ticket-sep">

        <!-- Info superior en dos columnas -->
        <div class="ticket-info">
            <span class="lbl">Tienda:</span>
            <span class="val">Celeste Boutique</span>

            <span class="lbl">Dirección:</span>
            <span class="val">Bogotá, Colombia</span>

            <span class="lbl">Fecha:</span>
            <span class="val"><?= date('m/d/Y H:i', strtotime($recibo['created_at'])) ?></span>

            <span class="lbl">N° Venta:</span>
            <span class="val">#<?= str_pad($recibo['id_venta'], 6, '0', STR_PAD_LEFT) ?></span>

            <span class="lbl">Cliente:</span>
            <span class="val"><?= htmlspecialchars($recibo['cliente_nombre']) ?></span>

            <span class="lbl">Cajero:</span>
            <span class="val"><?= htmlspecialchars($recibo['cajero']) ?></span>
        </div>

        <hr class="ticket-sep">

        <?php if ($recibo['estado'] === 'anulada'): ?>
        <div class="ticket-anulada">⚠ Venta Anulada</div>
        <?php endif; ?>

        <!-- Tabla de productos -->
        <table class="ticket-items">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align:right;">Precio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reciboDetalle as $d): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($d['producto']) ?>
                    <div class="item-qty">
                        <?= $d['cantidad'] ?> x $ <?= number_format($d['precio_unit'], 2, '.', ',') ?>
                    </div>
                </td>
                <td>$ <?= number_format($d['subtotal'], 2, '.', ',') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="ticket-sep dashed">

        <!-- Totales -->
        <div class="ticket-totals">
            <div class="row total">
                <span>Total</span>
                <span>$ <?= number_format($recibo['total'], 2, '.', ',') ?></span>
            </div>
        </div>

        <hr class="ticket-sep">

        <!-- Código de barras SVG -->
        <div class="ticket-barcode">
            <svg width="<?= min($barcodeWidth, 280) ?>" height="50"
                 viewBox="0 0 <?= $barcodeWidth ?> 50"
                 preserveAspectRatio="none"
                 xmlns="http://www.w3.org/2000/svg">
                <?= $barcode ?>
            </svg>
            <p class="ref"><?= $refNum ?></p>
        </div>

        <hr class="ticket-sep">

        <!-- Pie -->
        <p class="ticket-footer">¡Gracias por tu compra!</p>

    </div>
</div>
