<?php

namespace Daseraf\Debug\Model\Info;

class MemoryInfo
{
    public function getCurrentMemoryLimit()
    {
        return $this->convertToBytes(ini_get('memory_limit'));
    }

    public function getRealMemoryUsage()
    {
        return memory_get_peak_usage(true);
    }

    public function getMemoryUsage()
    {
        return memory_get_peak_usage();
    }

    private function convertToBytes($memoryLimit)
    {
        if ('-1' === $memoryLimit) {
            return -1;
        }

        $memoryLimit = strtolower($memoryLimit);
        $max = $this->readValue($memoryLimit);

        switch (substr($memoryLimit, -1)) {
            case 't':
                $max *= 1024;
                // no break
            case 'g':
                $max *= 1024;
                // no break
            case 'm':
                $max *= 1024;
                // no break
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    private function readValue($memoryLimit): int
    {
        $value = ltrim($memoryLimit, '+');
        if (0 === strpos($value, '0x')) {
            return intval($value, 16);
        }

        if (0 === strpos($value, '0')) {
            return intval($value, 8);
        }

        return (int) $value;
    }
}
