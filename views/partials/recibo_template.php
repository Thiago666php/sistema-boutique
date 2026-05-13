<?php
/**
 * Partial: recibo_template.php
 * Variables esperadas: $recibo (array), $reciboDetalle (array)
 */
?>
<div class="recibo-box" id="reciboImprimible">

    <div class="recibo-logo">
        <img src="../../img/logo1.png" alt="Celeste Boutique">
    </div>

    <h2 class="recibo-title">Celeste Boutique</h2>
    <p class="recibo-sub">Recibo de Venta</p>

    <hr class="recibo-divider">

    <div class="recibo-row">
        <span>N° Venta</span>
        <span>#<?= $recibo['id_venta'] ?></span>
    </div>
    <div class="recibo-row">
        <span>Fecha</span>
        <span><?= date('d/m/Y H:i', strtotime($recibo['created_at'])) ?></span>
    </div>
    <div class="recibo-row">
        <span>Cliente</span>
        <span><?= htmlspecialchars($recibo['cliente_nombre']) ?></span>
    </div>
    <div class="recibo-row">
        <span>Cajero</span>
        <span><?= htmlspecialchars($recibo['cajero']) ?></span>
    </div>
    <div class="recibo-row">
        <span>Estado</span>
        <span style="color:<?= $recibo['estado']==='completada'?'#1a7a4a':'#c0392b' ?>;font-weight:700;">
            <?= ucfirst($recibo['estado']) ?>
        </span>
    </div>

    <hr class="recibo-divider">

    <table class="recibo-tbl">
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align:center;">Cant.</th>
                <th style="text-align:right;">P. Unit.</th>
                <th style="text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reciboDetalle as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['producto']) ?></td>
            <td style="text-align:center;"><?= $d['cantidad'] ?></td>
            <td style="text-align:right;">$ <?= number_format($d['precio_unit'],2,',','.') ?></td>
            <td style="text-align:right;font-weight:600;">$ <?= number_format($d['subtotal'],2,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr class="recibo-divider">

    <div class="recibo-row">
        <span style="font-size:15px;font-weight:700;">TOTAL</span>
        <span class="recibo-total">$ <?= number_format($recibo['total'],2,',','.') ?></span>
    </div>

    <hr class="recibo-divider">

    <p style="text-align:center;font-size:11px;color:#7a8fa6;margin:8px 0 0;">
        ¡Gracias por tu compra! · Celeste Boutique<br>
        <?= date('d/m/Y H:i') ?>
    </p>
</div>
