<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formulario D.M. 11</title>
    <style>
        @page {
            margin: 20;
            margin-left: 30;
        }
        * {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-size: 12px
        }
        ul {
            /* margin-top: 5px;
            margin-bottom: 5px */
            margin: 0
        }
        .dashed {
            border-bottom: 1px dashed
        }
        .overlined {
            border-top: 1px dashed
        }
        .services {
            display: table-cell;
            width:33.333%;
            margin-right: -2px;
            margin-left: -2px;
            vertical-align: top
        }
    </style>
    <style>
        table,
        th,
        td {
          /* position: relative;
          border: 1px dashed black;
          border-collapse: collapse; */
          padding: 0
        }
        .label-cell {
            display: table-cell;
            width: 60px;
            position: relative
        }
        .label-cell::after {
            content: ":";
            position: absolute;
            top: 0;
            right: 0;
        }
      </style>
</head>
<body>
    <table style="width: 100%">
        <tbody>
            <tr>
                <td style="width: 100px; text-align: center"><img width="100px" height="100px" style="object-fit: cover" src="{{$logo}}"></td>
                <td style="padding: 10px" colspan="2" valign="top">
                    <div style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px">CAJA DE SALUD DE CAMINOS Y R.A.</div>
                    <div style="text-align: center; font-weight: bold; font-size: 18px; margin-top: 20px">SOLICITUD DE ATENCION EXTERNA</div>
                </td>
                <td style="width: 0" rowspan="3" valign="top">
                    <div style="text-align: center">
                        <div><span style="font-size: 8px">Form. D.M. - 11</span></div>
                        <img style="margin: 2px" src="data:image/svg+xml;base64,{{base64_encode(QrCode::size(160)->generate($qr_data)) }}">
                        <div><span>Nº {{$numero}}</span></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" valign="top">
                    <div style="display: table; width:100%; border-spacing:5px 10px; margin-top:-10px; margin-left:-5px">
                        <div style="display: table-row">
                            <div class="label-cell" style="display: table-cell;width: 60px">
                                <div>Fecha</div>
                            </div>
                            <div style="text-align: center; display: table-cell; border-bottom: 1px dashed">{{$regional}} {{$fecha}}</div>
                        </div> 
                        <div style="display: table-row">
                            <div  class="label-cell" style="display: table-cell;width: 60px">
                                <div>Señor Dr.</div>
                            </div>
                            <div style="text-align: center; display: table-cell; border-bottom: 1px dashed">{{$proveedor["razon_social"]}}</div>
                        </div>                    
                        <div style="display: table-row; margin-top: -10px">
                            <div style="display: table-cell">
                                <div>Presente</div>
                            </div>
                            <div style="font-size: 10px; text-align: center; display: table-cell">{{$proveedor["direccion"]}}. Telf.: {{$proveedor["telefono1"]}}, {{$proveedor["telefono2"]}}</div>
                        </div>                    
                    </div>
                </td>
                <td rowspan="3" valign="top" style="width: 225px">
                    <div style="width: 225px">
                        <div >
                            <div style="vertical-align: middle; width: 74px; display: inline-block"><span>Nº Carnet Asegurado</span></div>
                            <div style="text-align: center; vertical-align: middle; line-height: 24px; display: inline-block; border: 1px solid; border-top-left-radius: 4px; border-bottom-left-radius: 4px; height: 24px; width: 90px">
                            {{$titular["matricula"][0]}}</div><div style="text-align: center; vertical-align: middle; line-height: 24px; display: inline-block; margin-left: -1px; border: 1px solid; border-top-right-radius: 4px; border-bottom-right-radius: 4px; height: 24px; width: 45px">{{$titular["matricula"][1]}}</div>
                        </div>
                        <div >
                            <div style="vertical-align: middle; width: 74px; display: inline-block"><span>Nº Carnet Beneficiario</span></div>
                            <div style="text-align: center; vertical-align: middle; line-height: 24px;  display: inline-block; border: 1px solid; border-top-left-radius: 4px; border-bottom-left-radius: 4px; height: 24px; width: 90px">
                            {{$beneficiario["matricula"][0]}}</div><div style="text-align: center; vertical-align: middle; line-height: 24px;  display: inline-block; margin-left: -1px; border: 1px solid; border-top-right-radius: 4px; border-bottom-right-radius: 4px; height: 24px; width: 45px">{{$beneficiario["matricula"][1]}}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div style="position: absolute; top: 190px">
        <div style="text-align: center; font-weight: bold;">Agradecemos a Ud. tenga a bien prestar la siguiente atencion profesional</div>
        <div style="display:table; width:100%">
            <div class="services">
                <ul>
                    <li>{{$prestacion}}</li>
                </ul>
            </div>
        </div>
    
        <div style="display: table; width: 100%; margin-top: 10px; margin-bottom: 5px">
            <div style="display: table-cell; width: 70px">Al Paciente:</div>
            <div class="dashed" style="display: table-cell; height: 14px;">{{$beneficiario["nombre"]}}</div>
        </div>
        <div style="display: table; width: 100%; margin-bottom: 5px">
            <div style="display: table-cell; width: 128px">De nuestro asegurado:</div>
            <div class="dashed" style="display: table-cell; height: 14px;">{{$titular["nombre"]}}</div>
        </div>    
        <div style="display: table; width: 100%; margin-bottom: 5px">
            <div style="display: table-cell; width: 100px">A solicitud del Dr.:</div>
            <div class="dashed" style="display: table-cell; height: 14px;">{{$doctor["nombre"]}}</div>
            <div style="display: table-cell; width: 75px">Especialidad:</div>
            <div class="dashed" style="display: table-cell; height: 14px; width:150px; white-space:nowrap">{{$doctor["especialidad"]}}</div>
        </div>    
        <div style="display: table; width: 100%; margin-bottom: 10px">
            <div style="display: table-cell; width: 109px">Lugar de funciones:</div>
            <div class="dashed" style="display: table-cell; height: 14px;">{{$regional}}</div>
            <div style="display: table-cell; width: 58px">Empresa:</div>
            <div class="dashed" style="display: table-cell; height: 14px;">{{$empleador}}</div>
        </div>
        <div style="text-align: center; margin-bottom: 5px;">Enviando el correspondiente informe a nuestro Departamento Médico y devolviendonos esta solicitud adjunta a sus facturas (original y cuatro copias). en el plazo de TREINTA DÍAS, caso contrario no se reconocerá el importe.<br>Coneste motivo, saludamos a Ud.(s) con toda atención.</div>
        <div style="text-align: center; font-weight: bold">CAJA DE SALUD DE CAMINOS Y R.A.</div>
    </div>
    <div style="position: absolute; bottom: 20px; display: table; width: 100%;">
        <div style="text-align: center; display: table-cell;"><div class="overlined" style="margin-right: 20%;margin-left: 10%">Jefe Médico Regionar</div></div>
        <div style="text-align: center; display: table-cell;"><div class="overlined" style="margin-left: 20%;margin-right: 10%">Administrador Regional</div></div>
    </div>
</body>
</html>