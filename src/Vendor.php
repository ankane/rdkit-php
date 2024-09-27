<?php

namespace RDKit;

class Vendor
{
    public const VERSION = '2024.09.1';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'rdkit-{{version}}-x86_64-linux',
            'checksum' => '6f46533aaa7d4cc321325cddcc9ee20cb51001854ed3c92be38f97f0e37d2bd3',
            'lib' => 'librdkitcffi.so'
        ],
        'aarch64-linux' => [
            'file' => 'rdkit-{{version}}-aarch64-linux',
            'checksum' => '9aa68e89357ed9028919cbec768fd11c0516dffbb577d7411011c640a7aef9e9',
            'lib' => 'librdkitcffi.so'
        ],
        'x86_64-darwin' => [
            'file' => 'rdkit-{{version}}-x86_64-darwin',
            'checksum' => '848239591724e7a028fe73a267942c807e4abf7890781de82f64c82c67965c02',
            'lib' => 'librdkitcffi.dylib'
        ],
        'arm64-darwin' => [
            'file' => 'rdkit-{{version}}-aarch64-darwin',
            'checksum' => 'c4569e8e06cf97bdd1c9db87f3fa41120dbd6891ae599be4dc2e05313d69723f',
            'lib' => 'librdkitcffi.dylib'
        ],
        'x64-windows' => [
            'file' => 'rdkit-{{version}}-x86_64-windows',
            'checksum' => '098afb71e2521667eacd087fdfd3ffda8df33b7d6c9c2e75c5b26116d68a3be1',
            'lib' => 'rdkitcffi.dll'
        ]
    ];

    public static function check($event = null)
    {
        $dest = self::defaultLib();
        if (file_exists($dest)) {
            echo "✔ RDKit found\n";
            return;
        }

        $dir = self::libDir();
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        echo "Downloading RDKit...\n";

        $file = self::platform('file');
        $ext = 'zip';
        $url = self::withVersion("https://github.com/ankane/ml-builds/releases/download/rdkit-{{version}}/$file.$ext");
        $contents = file_get_contents($url);

        $checksum = hash('sha256', $contents);
        if ($checksum != self::platform('checksum')) {
            throw new \Exception("Bad checksum: $checksum");
        }

        $tempDest = tempnam(sys_get_temp_dir(), 'rdkit') . '.' . $ext;
        file_put_contents($tempDest, $contents);

        $archive = new \PharData($tempDest);
        if ($ext != 'zip') {
            $archive = $archive->decompress();
        }
        $archive->extractTo(self::libDir());

        echo "✔ Success\n";
    }

    public static function defaultLib()
    {
        return self::libDir() . '/' . self::libFile();
    }

    private static function libDir()
    {
        return __DIR__ . '/../lib';
    }

    private static function libFile()
    {
        return self::platform('lib');
    }

    private static function platform($key)
    {
        return self::PLATFORMS[self::platformKey()][$key];
    }

    private static function platformKey()
    {
        if (PHP_OS_FAMILY == 'Windows') {
            return 'x64-windows';
        } elseif (PHP_OS_FAMILY == 'Darwin') {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-darwin';
            } else {
                return 'arm64-darwin';
            }
        } else {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-linux';
            } else {
                return 'aarch64-linux';
            }
        }
    }

    private static function withVersion($str)
    {
        return str_replace('{{version}}', self::VERSION, $str);
    }
}
