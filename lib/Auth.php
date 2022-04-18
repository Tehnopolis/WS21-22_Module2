<?php

class Auth {
    // Encoding & decoding of JWT tokens
    public static function generateJwt(array $headers, array $payload, string $secret = 'sgk') {
        $headers_encoded = Auth::base64_url_encode(json_encode($headers));
        $payload_encoded = Auth::base64_url_encode(json_encode($payload));
        
        $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
        $signature_encoded = Auth::base64_url_encode($signature);
        
        $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
        
        return $jwt;
    }
    public static function decodeJwt(string $jwt, string $secret = 'sgk') {
        $tokenParts = explode('.', $jwt);
        $header = Auth::base64_url_decode($tokenParts[0]);
        $payload = Auth::base64_url_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        // Build signature
        $signature = hash_hmac('SHA256', $header . "." . $payload, $secret, true);
        $base64_url_signature = Auth::base64_url_decode($signature);

        // Verify signature
        $is_signature_valid = ($base64_url_signature === $signature_provided);
        
        return $payload;

        if (!$is_signature_valid) { return null; } 
        else { return json_decode($payload, true); }
    }

    // Base64 URL encoding & decoding
    private static function base64_url_encode(string $val) {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($val)
        );;
    }
    private static function base64_url_decode(string $val) {
        return str_replace(
            ['-', '_', ''],
            ['+', '/', '='],
            base64_decode($val)
        );;
    }
}