<?php

namespace App\Parser;

use Generator;
use RuntimeException;

class CSVFileParser
{
    public function parse($filePath, $header): Generator
    {
        if (!\is_file($filePath)) {
            throw new RuntimeException('File '.$filePath.' not found');
        }

        // Open the file for reading
        $file = fopen($filePath, "r");
        if ($file === false) {
            throw new RuntimeException('File '.$filePath.' can\'t be read');
        }

        try {
            $i = 0;
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $i++;
                if ($header === true && $i === 1) {
                    $header = $data;
                    continue;
                }

                yield $this->buildRow($data, $header);
            }
        } finally {
            fclose($file);
        }
    }

    private function buildRow(array $data, $header)
    {
        if (is_array($header)) {
            if (count($header) != count($data)) {
                throw new \RuntimeException('CSV header is invalid');
            }

            return \array_combine($header, $data);
        }

        return $data;
    }
}