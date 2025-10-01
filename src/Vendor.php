<?php

namespace RDKit;

class Vendor
{
    public const VERSION = '2025.09.1';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'rdkit-{{version}}-x86_64-linux',
            'checksum' => '2ce0732caedcc86882a634035a7c99a38638645a39eb0ebedc9e6af4c1bf1e57',
            'lib' => 'librdkitcffi.so'
        ],
        'aarch64-linux' => [
            'file' => 'rdkit-{{version}}-aarch64-linux',
            'checksum' => 'c4e815e8c9c8ec5d159d7bf7dc9274c79d74acad24a51f268d9f40f6f7b51e56',
            'lib' => 'librdkitcffi.so'
        ],
        'x86_64-darwin' => [
            'file' => 'rdkit-{{version}}-x86_64-darwin',
            'checksum' => '8ac1176a9cec3d37afaf208df09e8118994637083abc0db94a44df68f073c846',
            'lib' => 'librdkitcffi.dylib'
        ],
        'arm64-darwin' => [
            'file' => 'rdkit-{{version}}-aarch64-darwin',
            'checksum' => 'df4448ee962cfcfd1adabcfb70b6b4e4fc0289c4cc55bacf4a431e8bf0489b0b',
            'lib' => 'librdkitcffi.dylib'
        ],
        'x64-windows' => [
            'file' => 'rdkit-{{version}}-x86_64-windows',
            'checksum' => 'b37af610b9fdde2a2e5dee65c2e37fa3b524b52b9c4d69b00562f7906fa08f17',
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
