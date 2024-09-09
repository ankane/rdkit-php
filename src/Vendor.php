<?php

namespace RDKit;

class Vendor
{
    public const VERSION = '2024.03.6';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'rdkit-{{version}}-x86_64-linux',
            'checksum' => '8d5d9b33fe734a281aff399914480cfcde5d5777dd1fb51c3dc362241203776f',
            'lib' => 'librdkitcffi.so'
        ],
        'aarch64-linux' => [
            'file' => 'rdkit-{{version}}-aarch64-linux',
            'checksum' => 'cbe393edf6d7d6e94591a6475f6c1c93a2abb03c468573fa275b8816ad8b3dc2',
            'lib' => 'librdkitcffi.so'
        ],
        'x86_64-darwin' => [
            'file' => 'rdkit-{{version}}-x86_64-darwin',
            'checksum' => '1bc92460902c3ca9c566a1b480bb56d31ced14c62022c9cf090a006845ca86fa',
            'lib' => 'librdkitcffi.dylib'
        ],
        'arm64-darwin' => [
            'file' => 'rdkit-{{version}}-aarch64-darwin',
            'checksum' => '08e7e22338b99b5abc44e9e1d930409a6f2f9fadd8044f69114a0fcdbc736814',
            'lib' => 'librdkitcffi.dylib'
        ],
        'x64-windows' => [
            'file' => 'rdkit-{{version}}-x86_64-windows',
            'checksum' => 'c9d2758c2f9b6eb223745e7f0882f804a00de06f771715ce8706a10892d4829d',
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
