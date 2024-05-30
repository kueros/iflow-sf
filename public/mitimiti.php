<?php #echo "kdk" 
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitad Negra y Mitad Blanca</title>
    <!-- Enlace a Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .left-half {
            background-color: black;
            height: 100vh;
            /* Asegura que ocupa toda la altura de la pantalla */
        }

        .right-half {
            background-color: white;
            height: 100vh;
            /* Asegura que ocupa toda la altura de la pantalla */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 left-half">
                <!-- Contenido del lado izquierdo -->
            </div>
            <div class="col-md-6 right-half">
                <!-- Contenido del lado derecho -->
            </div>
        </div>
    </div>

    <!-- Enlace a jQuery y Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>