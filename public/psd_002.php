<?php
//error_reporting(0);
// Iniciar la sesión si aún no está iniciada

use Symfony\Component\VarDumper\VarDumper;


require_once '../vendor/autoload.php';
/*$conexion = new mysqli('localhost:8889', 'root', 'root', 'zeus_sp');
if ($conexion->connect_errno) {
	echo "ERROR al conectar con la DB.";
	exit;
}
*/
$nuevoPath = __DIR__ . '/..';
$dotenv = Dotenv\Dotenv::createImmutable($nuevoPath);
//$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
try {
	$dotenv->load();
} catch (Throwable $e) {
	die('Error al cargar el archivo .env: ' . $e->getMessage());
}
/*

// Set variables for our request
$api_key = $_ENV['CLI_ID'];              //$api_key = "1390429f3288965c898059e5860b21bf";
$shared_secret = $_ENV['CLI_PASS'];     //$shared_secret = "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";
$shop1 = $_ENV['SHOP_TEST'];

$fApiUsr = $_ENV['API_U'];
$fApiClave = $_ENV['API_P'];
*/
#dd($_GET);

#$shared_secret =  config('sfenv.shared_secret');

$params = $_GET;
$hmac = isset($_GET['hmac']) ? $_GET['hmac'] : '1';
$code = isset($_GET['code']) ? $_GET['code'] : '1';
$state = isset($_GET['state']) ? $_GET['state'] : '1';
$host = isset($_GET['host']) ? $_GET['host'] : '1';


$retorno = "http://localhost:8000/webhook?hmac=".$hmac;//."/".$code."/".$host."/".$state;
header("Location: " . $retorno);
die();


#var_dump($hmac);
#die();

$params = array_diff_key($params, array('hmac' => ''));

ksort($params);

$computer_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);
/*********************************************************************************/

$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
ksort($params); // Sort params lexographically

$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

// Use hmac data to check that the response is from Shopify or not
if (hash_equals($hmac, $computed_hmac)) {

	// Set variables for our request
	$query = array(
		"client_id" => $api_key, // Your API key
		"client_secret" => $shared_secret, // Your app credentials (secret key)
		"code" => $params['code'] // Grab the access key from the URL
	);
	$psd = $params['shop'];
	// Generate access token URL
	$access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

	// Configure curl client and execute request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $access_token_url);
	curl_setopt($ch, CURLOPT_POST, count($query));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
	$result = curl_exec($ch);
	curl_close($ch);

	// Store the access token
	$result = json_decode($result, true);
	#dd($result);
	$accessToken = $result['access_token'];

	// Show the access token (don't do this in production!)
	echo "token devuelto: $accessToken";
	#die();




	//Grabo en la base de datos la informacion de la tienda completa
	$sql = "INSERT INTO `datos`(`hmac`, 
                                 `host`, 
                                 `shop`, 
                                 `state`, 
                                 `fapiusr`, 
                                 `fapiclave`,
                                 `code`,
                                 `token`) 
            VALUES ('$hmac',
                    '$host',
                    '$shop1',
                    '$state',
                    '$fApiUsr',
                    '$fApiClave',
                    '$code',
                    '$accessToken');";


	$conexion->query($sql);

	#echo "token devuelto: $access_token";


	//********************************************* */
	// URL de tu webhook
	$webhook_url = 'http://localhost/sf/psd_004.php';
	// Configura la URL de la API de Shopify
	$api_url = "https://$shop1/admin/api/2024-07/webhooks.json";

	// Define los datos del webhook
	$webhook_data = [
		'webhook' => [
			'topic' => 'orders/create',
			'address' => $webhook_url,
			'format' => 'json'
		]
	];

	// Configura las opciones de la solicitud
	$options = [
		'http' => [
			'header' => "Content-type: application/json\r\n",
			'method' => 'POST',
			'content' => json_encode($webhook_data),
		],
	];

	// Realiza la solicitud para crear el webhook
	#	$context = stream_context_create($options);

	#	$result = file_get_contents("$api_url/webhooks.json", false, $context);
	#	die();
	$postFields = '{"webhook":{"address":"pubsub://projectName14:topicName","topic":"orders/delete","format":"json"}}';

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $postFields,
		CURLOPT_HTTPHEADER => array(
			'X-Shopify-Topic: orders/create',
			'X-Shopify-Shop-Domain: ' . $shop1,
			'X-Shopify-API-Version: 2024-07',
			'X-Shopify-Access-Token: ' . $accessToken,
			'Content-Type: application/json',
			'Cookie: request_method=POST'
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	#	echo 'asdf '.$response;
	$array = json_decode($response, true);

	// Verifica el resultado
	if ($result === FALSE) {

		$mensajeError =  "Error al crear el webhook";
		echo "<p style='color: red;'>$mensajeError</p>";
	} else {
		echo "Webhook creado exitosamente con ID " . $array['webhook']['id'];
	}







	/*************************************************
	exit;
} else {
	// Someone is trying to be shady!
	die('This request is NOT from Shopify!');
*/}
