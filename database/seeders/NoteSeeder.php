<?php

namespace Database\Seeders;

use App\Models\Dato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dato = new Dato;
        $dato->title = 'Aprendiendo Blade';
        $dato->content = <<<'CONTENT'
                    Para imprimir una variable con Blade utilizamos esta sintaxis:
                    `{{ $mi_variable }}`

                    Las directivas de Blade comienzan con arroba, por ejemplo:

                    `@foreach`
                    CONTENT;
        $dato->save();

        $dato = new Dato([
            'title' => '¿Para qué sirve Composer?',
            'content' => 'Con Composer podemos instalar y actualizar frameworks como Laravel o Symfony, así como componentes para generar PDF, procesar pagos con tarjetas, manipular imágenes y mucho más.',
        ]);
        $dato->save();

        Dato::create([
            'title' => 'Instalación de Laravel',
            'content' => <<<'CONTENT'
                    Hay 2 formas de instalar Laravel: la primera es a través con Composer,
                    la cual te permite instalar una versión específica de Laravel:

                    `composer create-project laravel/laravel curso-laravel-10 "10.*"`

                    La segunda es con el instalador de Laravel, la cual instalará la versión actual del framework:

                    `laravel new curso-laravel-10`
                    CONTENT,
        ]);

        Dato::create([
            'title' => 'Rutas y JSON',
            'content' => <<<'CONTENT'
                    Recuerda que si retornas un arreglo en una ruta, Laravel lo va a convertir en JSON automáticamente:

                    ```
                        <?php

                        Route::get('/', function () {
                            return [
                                'Cursos' => [
                                    'Primeros pasos con Laravel',
                                    'Crea un panel de control con Laravel',
                                ]
                            ];
                        });
                    ```

                    Producirá el siguiente resultado:

                    ```
                    {
                        "Cursos": [
                            "Primeros pasos con Laravel",
                            "Crea un panel de control con Laravel"
                        ]
                    }
                    ```
                    CONTENT,
        ]);

        Dato::create([
            'title' => 'Front Controller',
            'content' => 'Front Controller es un patrón de arquitectura donde un controlador maneja todas las solicitudes o peticiones a un sitio web.',
        ]);

        Dato::create([
            'title' => 'Cambia el formato de parámetros dinámicos',
            'content' => <<<'CONTENT'
                    Puedes colocar el siguiente código en el método `boot` de `app/Providers/RouteServiceProvider.php` para restringir cualquier parámetro de las rutas a un formato numérico:

                    ```
                    Route::pattern('nombre-del-parametro', '\d+');
                    ```

                    Puedes usar otras expresiones regulares para restringir a otros formatos.
                    CONTENT,
        ]);
    }
}
