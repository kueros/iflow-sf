<?php


# VER CON PABLO QUE DATOS HACEN FALTA PARA EL CURL
echo 'asdfasdfasfasdf';
var_dump($_GET);
var_dump($_POST);
die();
// Set variables for our request
$query = array(
    "client_id" => $_GET['api_u'], // Your API key
    "client_secret" => $_GET['api_p'], // Your app credentials (secret key)
    "code" => '' 
);
$url = "https://" . $_GET['shop'] . "/admin/oauth/access_token";

$response = makeCurlRequest( $uri = $url, $query );
$pseudoResponse = array(
                    'nombreTienda' => $_GET['shop'],
                    "client_id" => $_GET['api_u'],
                    "client_secret" => $_GET['api_p'],
                    );

#$install = "http://localhost:8000/envioCurl.php?api_u=" .$api_u ."&shop=" .$shop ."&api_p=".$api_p;

#header("Location: " .$install);
                    

echo $response;
die();

function makeCurlRequest($uri, $query)
{
    $curl = curl_init();
#    curl_setopt_array($curl, array(
    curl_setopt($curl, CURLOPT_URL, $uri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, count($query));
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
