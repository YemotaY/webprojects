<?php

namespace OPCUA;

class AddressSpace {
    private array $nodes = [];

    public function addNode(string $nodeId, array $attributes = []): void {
        if (isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId already exists.");
        }
        $this->nodes[$nodeId] = [
            'attributes' => array_merge([
                'displayName' => $nodeId,
                'browseName' => $nodeId,
                'nodeClass' => 'Object'
            ], $attributes),
            'references' => [],
            'variables' => [],
            'methods' => []
        ];
    }

    public function addVariable(string $nodeId, string $variableName, mixed $value, array $attributes = []): void {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        $this->nodes[$nodeId]['variables'][$variableName] = [
            'value' => $value,
            'attributes' => array_merge([
                'dataType' => gettype($value),
                'valueRank' => -1
            ], $attributes)
        ];
    }

    public function readVariable(string $nodeId, string $variableName): mixed {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        if (!isset($this->nodes[$nodeId]['variables'][$variableName])) {
            throw new OPCUAException("Variable $variableName does not exist in node $nodeId.");
        }
        return $this->nodes[$nodeId]['variables'][$variableName]['value'];
    }

    public function writeVariable(string $nodeId, string $variableName, mixed $value): void {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        if (!isset($this->nodes[$nodeId]['variables'][$variableName])) {
            throw new OPCUAException("Variable $variableName does not exist in node $nodeId.");
        }
        $this->nodes[$nodeId]['variables'][$variableName]['value'] = $value;
    }

    public function addMethod(string $nodeId, string $methodName, callable $callback, array $attributes = []): void {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        $this->nodes[$nodeId]['methods'][$methodName] = [
            'callback' => $callback,
            'attributes' => array_merge([
                'executable' => true
            ], $attributes)
        ];
    }

    public function callMethod(string $nodeId, string $methodName, ...$args): mixed {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        if (!isset($this->nodes[$nodeId]['methods'][$methodName])) {
            throw new OPCUAException("Method $methodName does not exist in node $nodeId.");
        }
        if (!$this->nodes[$nodeId]['methods'][$methodName]['attributes']['executable']) {
            throw new OPCUAException("Method $methodName is not executable in node $nodeId.");
        }
        return call_user_func_array($this->nodes[$nodeId]['methods'][$methodName]['callback'], $args);
    }

    public function addReference(string $sourceNodeId, string $referenceType, string $targetNodeId): void {
        if (!isset($this->nodes[$sourceNodeId])) {
            throw new OPCUAException("Source node $sourceNodeId does not exist.");
        }
        if (!isset($this->nodes[$targetNodeId])) {
            throw new OPCUAException("Target node $targetNodeId does not exist.");
        }
        $this->nodes[$sourceNodeId]['references'][] = [
            'referenceType' => $referenceType,
            'targetNodeId' => $targetNodeId
        ];
    }

    public function getNode(string $nodeId): array {
        if (!isset($this->nodes[$nodeId])) {
            throw new OPCUAException("Node with ID $nodeId does not exist.");
        }
        return $this->nodes[$nodeId];
    }

    public function listNodes(): array {
        return array_keys($this->nodes);
    }
}

// Example usage:
$space = new AddressSpace();

// Create a node
$space->addNode("Node1", ['displayName' => "Temperature Node"]);

// Add a variable
$space->addVariable("Node1", "temperature", 25.3, ['dataType' => 'Float']);

// Read a variable
echo $space->readVariable("Node1", "temperature") . PHP_EOL;

// Write a variable
$space->writeVariable("Node1", "temperature", 30.5);

// Add a method
$space->addMethod("Node1", "increaseTemp", function($increment) use ($space) {
    $current = $space->readVariable("Node1", "temperature");
    $new = $current + $increment;
    $space->writeVariable("Node1", "temperature", $new);
    return $new;
}, ['executable' => true]);

// Call the method
echo $space->callMethod("Node1", "increaseTemp", 5) . PHP_EOL;

// Add references
$space->addNode("Node2", ['displayName' => "Pressure Node"]);
$space->addReference("Node1", "Organizes", "Node2");

// List all nodes
print_r($space->listNodes());
