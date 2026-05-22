<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <h1>Prueba Select2 en Laravel</h1>

    <label for="cliente_id">Cliente</label>
    <select id="cliente_id" class="form-control" style="width: 100%"></select>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#cliente_id').select2({
                placeholder: 'Buscar cliente...',
                ajax: {
                    url: '/clientes/search',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });
    </script>
</body>
</html>
