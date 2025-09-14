<?php
// mpesa_token.php
$consumerKey = "NeuVkaSgFoYFMdzxwofGykSiUf3AEUFI8KkbPswq8CdUBHA7";
$consumerSecret = "oQSMGvlog3Afvh7Cl9H6GQc8B2I65GRK8VUyoAnjAlI2nhu3SiHxD5kGXf9lU4cg";

$credentials = base64_encode("$consumerKey:$consumerSecret");

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response);
$token = $data->access_token;

echo "Token: $token";
