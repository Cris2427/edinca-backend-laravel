<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1e3a6e; padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .body { padding: 30px; }
        .body h2 { color: #1e3a6e; }
        .detalle { background: #f8faff; border: 1px solid #dbeafe; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .detalle p { margin: 8px 0; }
        .detalle strong { color: #1e3a6e; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EDINCA</h1>
        </div>
        <div class="body">
            <h2>Estimado/a {{ $cotizacion->proyecto->cliente->nombre ?? 'Cliente' }},</h2>
            <p>Nos complace informarle que hemos preparado una cotización para su proyecto <strong>{{ $cotizacion->proyecto->nombre }}</strong>.</p>

            <div class="detalle">
                <p><strong>Proyecto:</strong> {{ $cotizacion->proyecto->nombre }}</p>
                <p><strong>Rango de precio:</strong>
                    ${{ number_format($cotizacion->precio_minimo, 0, ',', '.') }} CLP —
                    ${{ number_format($cotizacion->precio_maximo, 0, ',', '.') }} CLP
                </p>
                @if($cotizacion->tipo_material)
                <p><strong>Material:</strong> {{ $cotizacion->tipo_material }}</p>
                @endif
                @if($cotizacion->observaciones)
                <p><strong>Observaciones:</strong> {{ $cotizacion->observaciones }}</p>
                @endif
            </div>

            <p>Para consultas o más información, no dude en contactarnos.</p>
            <p>Atentamente,<br><strong>Equipo EDINCA</strong></p>
        </div>
        <div class="footer">
            <p>EDINCA — Edificaciones y Construcciones</p>
            <p>notificaciones@edinca.cl</p>
        </div>
    </div>
</body>
</html>
