<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Error!</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div class="wrap">
        <header class="head" style="background-color: orange;padding-left:0px; padding-right:15px; ">
            <!--a href="#" class="logo"></a-->
            <img src="{{ asset('../img/logoIf.png') }}" style=" height:4rem; padding-right:25px; padding-left:25px; background-color: black; " />
            <span style="padding-left:15px;">ERROR!!!</span>
        </header>
        <main class="content-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class=" cards">
                        <div class="card card-center">
                            <div class="card-header">Errores en la Instalación</div>
                            <div class="card-body">
                                <h1>¡¡¡ERROR!!!</h1>
                                <p class="error-message">La tienda ya ha sido instalada.</p>
                                <p class="error-message">Comuníquese con el Area Comercial.</p>
                                <a href="/" class="btn btn-danger">Volver atrás</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>