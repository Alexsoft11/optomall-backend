
<?php

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://1688-product2.p.rapidapi.com/1688/v2/item_detail?item_id=622619146604",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: 1688-product2.p.rapidapi.com",
		"X-RapidAPI-Key: ad1fe6ee4amsh7622e16403e7570p12c467jsnae077066fec7"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo $response;
}

?>
