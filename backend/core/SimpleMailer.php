<?php
class SimpleMailer {
    // Very small SMTP client supporting STARTTLS + AUTH LOGIN
    public static function sendSmtp(array $conf, $to, $subject, $body) {
        $host = $conf['smtp_host'] ?? 'smtp.gmail.com';
        $port = $conf['smtp_port'] ?? 587;
        $user = $conf['smtp_user'] ?? '';
        $pass = $conf['smtp_pass'] ?? '';
        $from = $conf['smtp_from'] ?? $user;
        $fromName = $conf['smtp_from_name'] ?? $from;
        $debugLog = $conf['debug_log'] ?? null;

        $log = function($msg) use ($debugLog) {
            if ($debugLog) file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        };

        $ctx = stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
        $fp = @stream_socket_client("tcp://" . $host . ":" . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) { $log("Could not connect to SMTP: $errno $errstr"); return false; }

        $recv = fgets($fp);
        $log("S: $recv");

        $send = function($cmd) use ($fp, $log) {
            fwrite($fp, $cmd . "\r\n");
            $log("C: $cmd");
            $res = '';
            while (($line = fgets($fp, 515)) !== false) {
                $res .= $line;
                if (isset($line[3]) && $line[3] == ' ') break;
            }
            $log("S: $res");
            return $res;
        };

        // EHLO
        $send("EHLO localhost");
        // STARTTLS
        $start = $send("STARTTLS");
        if (stripos($start, '220') === false) { $log('STARTTLS not supported or failed'); fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { $log('Failed to enable TLS'); fclose($fp); return false; }
        // EHLO again
        $send("EHLO localhost");

        // AUTH LOGIN
        $auth = $send("AUTH LOGIN");
        if (stripos($auth, '334') === false) { $log('AUTH LOGIN not initiated'); fclose($fp); return false; }
        $send(base64_encode($user));
        $resp = $send(base64_encode($pass));
        if (stripos($resp, '235') === false) { $log('Authentication failed: ' . $resp); fclose($fp); return false; }

        $send("MAIL FROM: <{$from}>");
        $send("RCPT TO: <{$to}>");
        $send("DATA");

        $headers = [];
        $headers[] = "From: {$fromName} <{$from}>";
        $headers[] = "To: {$to}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $send($data);
        $quit = $send("QUIT");
        fclose($fp);
        return stripos($quit, '221') !== false;
    }
}
