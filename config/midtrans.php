<?php 

require_once __DIR__ . '/../vendor/autoload.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-EDxDk3ODwW8iWXH8T1eeKpGQ';
\Midtrans\Config::$clientKey = 'SB-Mid-client-Ob4NdirpKZSup0Oe';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
// CALLBACK URL 
\Midtrans\Config::$appendNotifUrl = "https://sulfate-hurling-rematch.ngrok-free.dev/kasir-minimarket/callback_midtrans.php";
