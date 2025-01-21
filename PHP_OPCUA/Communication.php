<?php

namespace OPCUA;

class Communication
{
    private $serverSocket;
    private $clients = [];

    // Constants for OPC UA TCP defaults
    const OPC_TCP_PORT = 4840;
    const SESSION_TIMEOUT = 3600; // in seconds

    // Secure Channel Configuration
    private $aesKey;
    private $signatureAlgorithm;
    private $certificates;

    public function __construct()
    {
        $this->aesKey = random_bytes(32); // 256-bit key for AES
        $this->signatureAlgorithm = 'sha256';
        $this->certificates = [];
    }

    /**
     * Start the TCP Server
     */
    public function startServer($address = '0.0.0.0', $port = self::OPC_TCP_PORT)
    {
        $this->serverSocket = stream_socket_server("tcp://$address:$port", $errno, $errstr);

        if (!$this->serverSocket) {
            throw new \Exception("Failed to create server: $errstr ($errno)");
        }

        echo "Server started on $address:$port\n";

        while (true) {
            $clientSocket = @stream_socket_accept($this->serverSocket);
            if ($clientSocket) {
                $this->clients[] = $clientSocket;
                echo "New client connected\n";
                $this->handleClient($clientSocket);
            }
        }
    }

    /**
     * Handle TCP Client Communication
     */
    private function handleClient($clientSocket)
    {
        $data = fread($clientSocket, 1024);
        $decryptedData = $this->decryptData($data);

        // Process data (implement specific OPC UA logic here)
        $response = $this->encryptData("Response to: $decryptedData");
        fwrite($clientSocket, $response);

        fclose($clientSocket);
    }

    /**
     * Encrypt data using AES
     */
    public function encryptData($data)
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->aesKey, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data using AES
     */
    public function decryptData($data)
    {
        $decoded = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->aesKey, 0, $iv);
    }

    /**
     * Create and verify secure channels with certificates
     */
    public function addCertificate($clientId, $certificate)
    {
        // Add certificate verification logic here
        $this->certificates[$clientId] = $certificate;
    }

    public function verifyCertificate($clientId, $certificate)
    {
        return isset($this->certificates[$clientId]) && $this->certificates[$clientId] === $certificate;
    }

    /**
     * Create a new session
     */
    public function createSession($clientId)
    {
        $sessionId = bin2hex(random_bytes(16));
        $_SESSION[$clientId] = [
            'sessionId' => $sessionId,
            'createdAt' => time(),
            'timeout' => self::SESSION_TIMEOUT,
        ];

        return $sessionId;
    }

    /**
     * Validate session
     */
    public function validateSession($clientId, $sessionId)
    {
        if (!isset($_SESSION[$clientId])) {
            return false;
        }

        $session = $_SESSION[$clientId];
        if ($session['sessionId'] !== $sessionId) {
            return false;
        }

        if (time() - $session['createdAt'] > $session['timeout']) {
            unset($_SESSION[$clientId]);
            return false;
        }

        return true;
    }

    /**
     * Stop the server and close all sockets
     */
    public function stopServer()
    {
        foreach ($this->clients as $client) {
            fclose($client);
        }

        fclose($this->serverSocket);
        echo "Server stopped\n";
    }

    /**
     * Getter für den Server-Socket
     */
    public function getServerSocket()
    {
        return $this->serverSocket;
    }
}
