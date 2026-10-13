{{-- Mail de una consulta del formulario del landing. HTML simple y con estilos
     en línea: los clientes de correo no soportan hojas de estilo externas. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta web</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:24px 12px">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:620px;background:#ffffff;border:1px solid #e4e4e7">

                {{-- Encabezado --}}
                <tr>
                    <td style="background:#15171a;padding:18px 24px">
                        <div style="color:#ffffff;font-size:17px;font-weight:bold">Nueva consulta desde la web</div>
                        <div style="color:#b8bcc2;font-size:12px;margin-top:3px">
                            plote.ar · {{ $consulta->created_at->format('d/m/Y H:i') }} hs
                        </div>
                    </td>
                </tr>

                {{-- Datos --}}
                <tr>
                    <td style="padding:22px 24px">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px">
                            <tr>
                                <td style="padding:7px 0;width:150px;color:#6b7280">Nombre / Empresa</td>
                                <td style="padding:7px 0;font-weight:bold">{{ $consulta->nombre }}</td>
                            </tr>
                            <tr>
                                <td style="padding:7px 0;color:#6b7280;border-top:1px solid #f1f1f3">Email</td>
                                <td style="padding:7px 0;border-top:1px solid #f1f1f3">
                                    <a href="mailto:{{ $consulta->email }}" style="color:#e6502a">{{ $consulta->email }}</a>
                                </td>
                            </tr>
                            @if($consulta->telefono)
                            <tr>
                                <td style="padding:7px 0;color:#6b7280;border-top:1px solid #f1f1f3">WhatsApp / Teléfono</td>
                                <td style="padding:7px 0;border-top:1px solid #f1f1f3">
                                    @php $wa = $consulta->whatsappUrl(); @endphp
                                    @if($wa)
                                        <a href="{{ $wa }}" style="color:#e6502a">{{ $consulta->telefono }}</a>
                                    @else
                                        {{ $consulta->telefono }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($consulta->servicio)
                            <tr>
                                <td style="padding:7px 0;color:#6b7280;border-top:1px solid #f1f1f3">Servicio</td>
                                <td style="padding:7px 0;border-top:1px solid #f1f1f3;font-weight:bold">{{ $consulta->servicio }}</td>
                            </tr>
                            @endif
                        </table>

                        {{-- Mensaje --}}
                        <div style="margin-top:18px;padding-top:14px;border-top:2px solid #15171a">
                            <div style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:bold">
                                Mensaje
                            </div>
                            <div style="margin-top:8px;font-size:14px;line-height:1.6;white-space:pre-line">{{ $consulta->mensaje }}</div>
                        </div>
                    </td>
                </tr>

                {{-- Pie --}}
                <tr>
                    <td style="background:#fafafa;padding:14px 24px;border-top:1px solid #e4e4e7;font-size:11px;color:#6b7280">
                        Respondiendo este mail le contestás directamente a {{ $consulta->email }}.<br>
                        La consulta también quedó guardada en el panel (consulta #{{ $consulta->id }}).
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
