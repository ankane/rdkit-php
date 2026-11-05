<?php

namespace RDKit;

class Vendor
{
    public const VERSION = '2026.09.2';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'rdkit-{{version}}-x86_64-linux',
            'checksum' => '4646c9f5767ea60824b4e2f750ab8d7ed1a8150341d626345e0aa87c3c47f138',
            'lib' => 'librdkitcffi.so'
        ],
        'aarch64-linux' => [
            'file' => 'rdkit-{{version}}-aarch64-linux',
            'checksum' => 'c8bb5ba3e03900f2656bb93b0fbf56839da8e8fb91bf08e22a1f9d1da5d1720a',
            'lib' => 'librdkitcffi.so'
        ],
        'x86_64-darwin' => [
            'file' => 'rdkit-{{version}}-x86_64-darwin',
            'checksum' => '169ffdbfed2a59425e6777ad598ed64c7b8f6de0de7100b19ce7de2a8d127bc6',
            'lib' => 'librdkitcffi.dylib'
        ],
        'arm64-darwin' => [
            'file' => 'rdkit-{{version}}-aarch64-darwin',
            'checksum' => 'cd9b43d9db36dce77ab68efeeaa9c8196d8393bba4fa12dac496c411384f4bab',
            'lib' => 'librdkitcffi.dylib'
        ],
        'x64-windows' => [
            'file' => 'rdkit-{{version}}-x86_64-windows',
            'checksum' => 'a01a899f0275dab415f687268c1093ce935bc397b88af597cdbf664df0680cc3',
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
