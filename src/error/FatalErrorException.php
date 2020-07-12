<?php
namespace error;

class FatalErrorException extends \Exception {
    public function __construct(string $message, int $code = null, string $file, int $line) {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}