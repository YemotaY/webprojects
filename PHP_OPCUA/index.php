<?php
//http://127.0.0.1/php_opcua/
namespace OPCUA;
require "Communication.php";
require "Client.php";
echo("HI");
// Beispiel Server-Kommunikation starten
$server = new Communication();
try {
    // Starten Sie den Server auf einem bestimmten Port
    $server->startServer();
} catch (\Exception $e) {
    echo $e->getMessage();
}

$clientSocket = stream_socket_accept($server->getServerSocket()); // Zugriff auf den Server-Socket
$opcuaClient = new OPCUAClient($server, $clientSocket);

// Subscription auf eine Variable (z.B. Temperatur)
$opcuaClient->subscribeVariable("ns=2;s=Temperature", function($newValue) {
    echo "Benachrichtigung: Temperatur geändert! Neuer Wert: $newValue\n";
});

// Subscription auf ein Ereignis (z.B. Alarm)
$opcuaClient->subscribeEvent("Alarm", function($eventData) {
    echo "Benachrichtigung: Alarm ausgelöst! Daten: $eventData\n";
});

// Knoten durchsuchen
$nodes = $opcuaClient->browse("ns=2;");
echo "Gefundene Knoten:\n";
print_r($nodes);

// Knoten-Details anzeigen
$nodeDetails = $opcuaClient->getNodeDetails("ns=2;s=Temperature");
echo "Details zum Knoten:\n";
print_r($nodeDetails);

// Simuliere Benachrichtigungen
$opcuaClient->notifySubscriptions("ns=2;s=Temperature", "25.0°C");
$opcuaClient->notifyEvent("Alarm", "Fehlfunktion erkannt!");

// Stoppen Sie den Server (normalerweise am Ende)
$server->stopServer();
