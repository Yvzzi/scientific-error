<?php
namespace error;

class ScientificError {
    protected static $hideOnWeb = false;
    protected static $showSource = true;
    protected static $throwError = false;
    protected static $strictTreatWithError = true;
    protected static $paths = [];

    public static function init() {
        ini_set("display_errors", "on");
        ini_set("error_reporting", E_ALL);

        set_exception_handler(function (\Throwable $e) {
            self::exceptionHandler($e);
        });
        set_error_handler(function ($type, $message, $file, $line) {
            self::errorHandler($type, $message, $file, $line);
        }, E_ALL | E_STRICT);
        register_shutdown_function(function () {
            self::shutdownErrorHandler();
        });
    }

    public static function hideOnWeb(bool $hide = true) {
        self::$hideOnWeb = $hide;
    }

    public static function showSource(bool $show = true) {
        self::$showSource = $show;
    }

    public static function hatePath(string $path, string $replacement = "@") {
        array_push(self::$paths, [realpath($path), "{" . $replacement . "}"]);
    }

    public static function throwError(bool $throw = true) {
        self::$throwError = $throw;
    }

    public static function strictTreatWithError(bool $strict = true) {
        self::$strictTreatWithError = $strict;
    }

    protected static function shutdownErrorHandler() {
        if ($error = error_get_last()) {
            $type = $error["type"];
            $message = $error["message"];
            $file = $error["file"];
            $line = $error["line"];
            $map = [
                E_ERROR => "ERROR",
                E_PARSE => "PARSE ERROR",
                E_CORE_ERROR => "CORE ERROR",
                E_CORE_WARNING => "CORE WARINING",
                E_COMPILE_ERROR => "COMPILE ERROR",
                E_COMPILE_WARNING => "COMPILE WARNING",
                E_STRICT => "STRICT ERROR"
            ];

            $buf = "[" . ($map[$type] ?? $type) . "]" . $message . " in "
                . self::makeShorterPath($file) . "(" . $line . ")\n";

            if (!self::$throwError) {
                if (self::$showSource)
                    $buf .= "\n" . self::getSourceAroundLine($file, $line) . "\n";
                self::print($buf);
            } else {
                throw new FatalErrorException($message, $type, $file, $line);
            }
        }
    }

    protected static function errorHandler($type, $message, $file, $line) {
        if (!(error_reporting() & $type)) {
            return false;
        }
        // E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING
        // independent of where they were raised, and most of E_STRICT
        $map = [
            E_WARNING => "WARNING",
            E_USER_WARNING => "WARNING",
            E_NOTICE => "NOTICE",
            E_USER_NOTICE => "NOTICE",
            E_STRICT => "STRICT",
            E_DEPRECATED => "DEPRECATED",
            E_USER_DEPRECATED => "DEPRECATED"
        ];

        $buf =  "[" . ($map[$type] ?? $type) . "] " . $message . " in "
            . self::makeShorterPath($file) . "(" . $line . ")\n";

        if (self::$showSource)
            $buf .= "\n" . self::getSourceAroundLine($file, $line) . "\n";

        if (!self::$throwError) {
            self::print($buf);

            if (self::$strictTreatWithError && ($type & (E_ALL | E_STRICT)))
                exit;
        } else {
            throw new FatalErrorException($message, $type, $file, $line);
        }
    }

    protected static function exceptionHandler(\Throwable $e) {
        $buf = "";
        $buf .= get_class($e) . "(" . $e->getCode() . "): " . $e->getMessage() . "\n"
            . "in " . self::makeShorterPath($e->getFile()) . "(" . $e->getLine() . ")\n"
            . self::makeShorterPath($e->getTraceAsString()) . "\n";

        if (self::$showSource) {
            $buf .= "\n" . self::getSourceAroundLine($e->getFile(), $e->getLine());
        }

        self::print($buf);
    }

    protected static function print(string $buf) {
        if (substr(php_sapi_name(), 0, 3) === "cli") {
            echo $buf . "\n";
        } else {
            if (self::$hideOnWeb)
                return;
            echo preg_replace("#\r?\n#", "<br/>", $buf . "\n");
        }
    }

    protected static function makeShorterPath(string $str) {
        foreach (self::$paths as [$path, $alias]) {
            $str = str_replace($path, $alias, $str);
        }
        return $str;
    }

    protected static function getSourceAroundLine(string $file, int $line) {
        $str = explode("\n", file_get_contents($file));
        $buf = "";
        $len = strlen(strval($line + 2));
        $buf .= basename($file) . ":\n";
        for ($i = -3; $i < 3; $i++) {
            if (isset($str[$line + $i - 1])) {
                $char = $i === 0 ? "x" : "|";
                $buf .= sprintf("% {$len}s", $line + $i) . " " . $char . " " . $str[$line + $i - 1] . "\n";
            }
        }
        return $buf;
    }
}
