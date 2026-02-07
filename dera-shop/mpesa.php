<?php
require_once 'mpesa_config.php';

class Mpesa {
    private $base_url = 'https://sandbox.safaricom.co.ke'; // Change to 'https://api.safaricom.co.ke' for LIVE
    
    public function getToken() {
        $credentials = base64_encode(CONSUMER_KEY . ':' . CONSUMER_SECRET);
        $url = $this->base_url . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($status == 200) {
            $data = json_decode($response);
            return $data->access_token;
        }
        return false;
    }
    
    public function stkPush($phone, $amount, $account_ref = 'DeraShop', $order_id = '') {
        $token = $this->getToken();
        if (!$token) return false;
        
        $timestamp = date('YmdHis');
        $password = base64_encode(SHORTCODE . PASSKEY . $timestamp);
        
        $data = [
            'BusinessShortCode' => SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => SHORTCODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => CALLBACK_URL,
            'AccountReference' => $account_ref,
            'TransactionDesc' => "Payment for Order #$order_id"
        ];
        
        $url = $this->base_url . '/mpesa/stkpush/v1/processrequest';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}
?>
