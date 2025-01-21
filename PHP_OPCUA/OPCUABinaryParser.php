<?php

namespace OPCUA;

class OPCUABinaryParser {
    private $data;
    private $offset;
    private $isLittleEndian;

    public function __construct(string $binaryData, bool $isLittleEndian = true) {
        $this->data = $binaryData;
        $this->offset = 0;
        $this->isLittleEndian = $isLittleEndian;
    }

    private function validateLength(int $length): void {
        if ($this->offset + $length > strlen($this->data)) {
            throw new \RuntimeException("Invalid data: Attempt to read beyond the available data.");
        }
    }

    public function readBoolean(): bool {
        $this->validateLength(1);
        return $this->readUInt8() !== 0;
    }

    public function readUInt8(): int {
        $this->validateLength(1);
        return unpack("C", $this->readBytes(1))[1];
    }

    public function readInt16(): int {
        $this->validateLength(2);
        return $this->isLittleEndian ? 
            unpack("v", $this->readBytes(2))[1] : 
            unpack("n", $this->readBytes(2))[1];
    }

    public function readUInt16(): int {
        $this->validateLength(2);
        return $this->isLittleEndian ? 
            unpack("v", $this->readBytes(2))[1] : 
            unpack("n", $this->readBytes(2))[1];
    }

    public function readInt32(): int {
        $this->validateLength(4);
        return $this->isLittleEndian ? 
            unpack("V", $this->readBytes(4))[1] : 
            unpack("N", $this->readBytes(4))[1];
    }

    public function readUInt32(): int {
        $this->validateLength(4);
        return $this->isLittleEndian ? 
            unpack("V", $this->readBytes(4))[1] : 
            unpack("N", $this->readBytes(4))[1];
    }

    public function readFloat(): float {
        $this->validateLength(4);
        $bytes = $this->readBytes(4);
        if (!$this->isLittleEndian) {
            $bytes = strrev($bytes);
        }
        return unpack("f", $bytes)[1];
    }

    public function readDouble(): float {
        $this->validateLength(8);
        $bytes = $this->readBytes(8);
        if (!$this->isLittleEndian) {
            $bytes = strrev($bytes);
        }
        return unpack("d", $bytes)[1];
    }

    public function readString(): ?string {
        $length = $this->readUInt32();
        if ($length === 0xFFFFFFFF) {
            return null; // Null string
        }
        $this->validateLength($length);
        return $this->readBytes($length);
    }

    public function readArray(callable $itemReader): array {
        $length = $this->readUInt32();
        if ($length === 0xFFFFFFFF) {
            return []; // Null array
        }
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = $itemReader($this);
        }
        return $result;
    }

    private function readBytes(int $length): string {
        $this->validateLength($length);
        $bytes = substr($this->data, $this->offset, $length);
        $this->offset += $length;
        return $bytes;
    }

    public function getOffset(): int {
        return $this->offset;
    }

    public function setEndian(bool $isLittleEndian): void {
        $this->isLittleEndian = $isLittleEndian;
    }

    public function isLittleEndian(): bool {
        return $this->isLittleEndian;
    }
}

// Beispielnutzung:
try {
    $binaryData = "\x01\x00\x00\x00Hello, OPC UA!";
    $parser = new OPCUABinaryParser($binaryData);

    // Lies einen Boolean
    $boolean = $parser->readBoolean();
    echo "Boolean: " . ($boolean ? "true" : "false") . "\n";

    // Lies einen String
    $string = $parser->readString();
    echo "String: $string\n";

    // Versuch, ungültige Daten zu lesen (führt zu einer Ausnahme)
    $parser->readUInt32(); // Daten sind unvollständig
} catch (\RuntimeException $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
}
