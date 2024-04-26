<?php

namespace App\Http\Controllers;

use App\Models\Dato;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;

class DatoController
{
    public function index()
    {

        $datos = Dato::query()
            ->orderByDesc('id')
            ->get();

        return view('datos.index')->with('datos', $datos);
    }

    public function show(int $id)
    {
        return 'Detalle de la Tienda: ' . $id;
    }

    public function create()
    {
        return view('datos.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'shop' => 'required',
            'fApiUsr' => 'required',
            'fApiClave' => 'required',
        ]);

        Dato::create([
            'shop' => $request->input('shop'),
            'fApiUsr' => $request->input('fApiUsr'),
            'fApiClave' => $request->input('fApiClave'),
        ]);

        /* DATOS DEL ENV */
        #Cargo datos del env en variables
        $api_key = config('sfenv.api_key');
        $redirect_url =  config('sfenv.redirect_url');
        $scope =  config('sfenv.scope');
        #dd($api_key.PHP_EOL.$redirect_url.PHP_EOL.$scope);

        #Chupo los datos del formulario
        $shop = $_POST["shop"];
        $api_u = $_POST["fApiUsr"];
        $api_p = $_POST["fApiClave"];


        #       return Redirect::route('psd1.index');
        #die();

        $install = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scope . "&redirect_uri=" . $redirect_url;

        header("Location: " . $install);

        echo "volvimos";
        die();


        $query = array(
            "client_id" => 'a63d7f3c5ab33dd09bd3424b58d83782', // Your API key
            "client_secret" => 'd02d01e10a952e9dc090e67ed8e1f138', // Your app credentials (secret key)
            "code" => ''
        );
        $url = "https://" . $shop . "/admin/oauth/access_token";



        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, count($query));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;
        die();



        return to_route('datos.index');
    }

    public function edit($id)
    {
        $dato = Dato::findOrFail($id);

        return view('datos.edit', ['dato' => $dato]);
    }

    public function update($id, Request $request)
    {
        $dato = Dato::findOrFail($id);

        $request->validate([
            'shop' => 'required',
            'fApiUsr' => 'required',
            'fApiClave' => 'required',
        ]);

        $dato->update([
            'shop' => $request->input('shop'),
            'fApiUsr' => $request->input('fApiUsr'),
            'fApiClave' => $request->input('fApiClave'),
        ]);

        return to_route('datos.index');
    }

    public function destroy($id)
    {
        $dato = Dato::findOrFail($id);

        $dato->delete();

        return to_route('datos.index');
    }
}
