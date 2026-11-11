{{-- Upload rules shown in the "Importar Lista de Espera" modal. Keep in sync with App\Imports\DemandManagementWaitlistImport. --}}
@php
    $section = 'margin-top: .75rem; font-weight: 600;';
    $list = 'list-style: disc; padding-left: 1.25rem; margin-top: .25rem;';
@endphp

<details style="font-size: .875rem; line-height: 1.45;">
    <summary style="cursor: pointer; font-weight: 600;">Instrucciones y reglas de carga</summary>

    <p style="{{ $section }}">Cómo cargar</p>
    <ol style="list-style: decimal; padding-left: 1.25rem; margin-top: .25rem;">
        <li>Use el formato SIGTE: las mismas columnas que entrega el botón <b>Exportar</b>. La primera fila debe tener los nombres de columna.</li>
        <li>Suba el archivo <b>.xlsx</b> (o <b>.csv</b> separado por comas) y presione <b>Importar</b>.</li>
        <li>La carga se procesa en segundo plano. Al terminar llega una notificación a la campana con el resumen y los botones <b>Descargar cambios</b> y <b>Descargar rechazados</b>; los archivos quedan disponibles por 1 día.</li>
        <li>Corrija las filas del Excel de rechazados (columna <b>MOTIVO RECHAZO</b>) y vuelva a subir ese mismo archivo; las columnas extra se ignoran.</li>
    </ol>

    <p style="{{ $section }}">Archivo</p>
    <ul style="{{ $list }}">
        <li>Se importa solo la <b>primera hoja</b> que tenga las columnas RUN, DV, TIPO_PREST, PRESTA_MIN y F_ENTRADA. Las demás hojas (resúmenes, cortes anteriores) se ignoran. Si ninguna hoja las tiene, la carga se rechaza completa.</li>
        <li>Los nombres de columna no distinguen mayúsculas ni tildes. Las columnas desconocidas y las filas vacías se ignoran.</li>
    </ul>

    <p style="{{ $section }}">Casos nuevos y actualizaciones</p>
    <ul style="{{ $list }}">
        <li>El paciente se identifica por <b>RUN</b> y el caso por <b>RUN + SIGTE_ID</b>. Si el caso existe se actualiza; si no, se crea.</li>
        <li>Los valores del archivo <b>reemplazan</b> a los registrados, incluidas las celdas vacías (excepto CIE-10 y GES cuando su columna no viene en el archivo).</li>
        <li>Cada caso nuevo o modificado queda registrado en la pestaña <b>Cargas</b> con el detalle de los datos que cambiaron.</li>
    </ul>

    <p style="{{ $section }}">Formatos</p>
    <ul style="{{ $list }}">
        <li>Fechas: <b>dd-mm-aaaa</b>, <b>dd/mm/aaaa</b> o fecha de Excel.</li>
        <li>SEXO: 1 Hombre, 2 Mujer, 3 Intersex, 93 No informado, 99 Desconocido.</li>
        <li>COND_RURALIDAD: 1 Urbano, 2 Rural. VIA_DIRECCION: 1 Calle, 2 Pasaje, 3 Avenida, 4 Otro.</li>
        <li>COMUNA, ESTAB_ORIG y ESTAB_DEST: código DEIS. Un código que no existe queda en blanco (no rechaza la fila).</li>
        <li>GES: Sí / No (también se aceptan S, 1, X).</li>
    </ul>

    <p style="{{ $section }}">Una fila se rechaza (no se importa) si</p>
    <ul style="{{ $list }}">
        <li><b>TIPO_PREST 1</b> (consulta) y PRESTA_MIN o PRESTA_MIN_SALIDA no es un código de especialidad del catálogo. Se aceptan el código nuevo (07-xxx / 09-xxx) y el antiguo.</li>
        <li><b>TIPO_PREST 3</b> (procedimiento) y PRESTA_MIN o PRESTA_MIN_SALIDA no es un código de procedimiento SIGTE (ej. 13-01-021).</li>
        <li>PRESTA_MIN es un código de procedimiento pero TIPO_PREST no es 3.</li>
        <li>La edad del paciente a la F_ENTRADA (según FECHA_NAC) está fuera del rango de la especialidad, p. ej. Odontopediatría 0 – 15 años (15 años y 11 meses todavía es válido).</li>
        <li>Un código antiguo corresponde a varias especialidades y la edad no permite elegir una: use el código nuevo.</li>
        <li>El CIE-10 no existe en el catálogo. Se toma de la columna <b>CIE-10</b> o, si no viene, de CONFIR_DIAG / SOSPECHA_DIAG cuando contienen solo un código (ej. K07.2).</li>
        <li>GES = Sí, pero el CIE-10 no pertenece a ningún problema de salud GES. Si no viene la columna GES, se calcula a partir del CIE-10.</li>
    </ul>
</details>
