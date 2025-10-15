<?php

function AltiriaSMS($sDestination, $sMessage, $sSenderId, $debug){
	if($debug)        
	echo 'Enter AltiriaSMS <br/>';

	//URL base de los recursos REST
	$baseUrl = 'https://www.altiria.net:8443/apirest/ws';
	 
	//Se inicia el objeto CUrl 
	$ch = curl_init($baseUrl.'/sendSms');

	//YY y ZZ se corresponden con los valores de identificaci�n del
	//usuario en el sistema.
	$credentials = array(
	    'login'    => 'YY',
	    'passwd'   => 'ZZ'
	);
	
	//Descomentar para utilizar la autentificaci�n mediante apikey
	$credentials = array(
	 'apiKey'    => 'mz7Y5j47vK',
	 'apiSecret' => 'a7fcgcxbme'
	);

        $destinations = explode(',', $sDestination);

        $jsonMessage = array(
	    'msg' => substr($sMessage,0,160),
	    'senderId' => $sSenderId 
	);

	$jsonData = array(
	    'credentials' => $credentials, 
	    'destination' => $destinations,
	    'message'     => $jsonMessage
	);
	 
	//Se construye el mensaje JSON
	$jsonDataEncoded = json_encode($jsonData);
	 
	//Indicamos que nuestra petici�n sera Post
	curl_setopt($ch, CURLOPT_POST, 1);

	//Se fija el tiempo m�ximo de espera para conectar con el servidor (5 segundos)
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	 
	//Se fija el tiempo m�ximo de espera de la respuesta del servidor (60 segundos)
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	 
	//Para que la peticion no imprima el resultado como un 'echo' comun
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 
	//Se a�ade el JSON al cuerpo de la petici�n codificado en UTF-8
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
	 
	//Se fija el tipo de contenido de la peticion POST
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8'));

	//Se env�a la petici�n y se consigue la respuesta
	$response = curl_exec($ch);

	$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if($debug) {   
		//Error en la respuesta del servidor  	
		if($statusCode != 200){ 
			echo 'ERROR GENERAL: '.$statusCode;
			echo $response;
		}else{
			//Se procesa la respuesta capturada 
			echo 'Codigo de estado HTTP: '.$statusCode.'<br/>';
			$json_parsed = json_decode($response);
			$status = $json_parsed->status;
			echo 'Codigo de estado Altiria: '.$status.'<br/>';
			if ($status != '000')
				echo 'Error: '.$response.'<br/>';
			else{
				echo 'Cuerpo de la respuesta: <br/>';
				foreach ($json_parsed->details as $i => $detail) {
					echo "details[$i][destination]: " . $detail->destination . "<br/>";
					echo "details[$i][status]: " . $detail->status . "<br/>";
				}
			}
		}
	}

	//Si ha ocurrido algn error se lanza una excepcin
	if(curl_errno($ch))
	    throw new Exception(curl_error($ch));

	return $response;
}

try{
    echo "The function AltiriaSMS returns: ".AltiriaSMS('14752661178','Mensaje de prueba desde PHP verificar si ha llegado y confirmar.', '', true);    
}catch(Exception $e){
   echo 'Error: '.$e->getMessage();
}

?>
