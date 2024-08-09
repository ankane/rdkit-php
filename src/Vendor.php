<?php

namespace RDKit;

class Vendor
{
    public const VERSION = '2024.03.5';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'rdkit-{{version}}-x86_64-linux',
            'checksum' => '32de5f5ef43617233b8d4ced4679076612038e756150c60d8f487bf9761fe77d',
            'lib' => 'librdkitcffi.so'
        ],
        'aarch64-linux' => [
            'file' => 'rdkit-{{version}}-aarch64-linux',
            'checksum' => '4530b3aef0688b7c63d849eace6008e2eed978d2d016cdebe54f57ec81f4ca22',
            'lib' => 'librdkitcffi.so'
        ],
        'x86_64-darwin' => [
            'file' => 'rdkit-{{version}}-x86_64-darwin',
            'checksum' => '5349e4cdaacf58895beb6370c9b4ecd5764834ab4beef9619e927f738ac256bc',
            'lib' => 'librdkitcffi.dylib'
        ],
        'arm64-darwin' => [
            'file' => 'rdkit-{{version}}-aarch64-darwin',
            'checksum' => 'ac2cb3d8c3e1364919bf6ddc567639bcaf456a617fc84ae0546bcdfe6716ce61',
            'lib' => 'librdkitcffi.dylib'
        ],
        'x64-windows' => [
            'file' => 'rdkit-{{version}}-x86_64-windows',
            'checksum' => '12af7ca31966e08abe96759422e2607dc65ed0e7aab02605303105a5791c87b5',
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
