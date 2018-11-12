<?php
/**
 * Created by PhpStorm.
 * User: lzning
 * Date: 2018/11/12
 * Time: 1:58 PM
 */

namespace Zning\LaravelUpYun;


use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Upyun\Upyun;

class UpyunAdapter extends AbstractAdapter
{

    protected $serverName;
    protected $operatorName;
    protected $operatorPassword;
    protected $domain;
    protected $protocol;

    public function __construct($serverName, $operatorName, $operatorPassword, $domain, $protocol = 'http')
    {
        $this->serverName = $serverName;
        $this->operatorName = $operatorName;
        $this->operatorPassword = $operatorPassword;
        $this->domain = $domain;
        $this->protocol = $protocol;

    }


    protected function client()
    {
        $config = new \Upyun\Config($this->serverName, $this->operatorName, $this->operatorPassword);
        $config->useSsl = config('filesystems.disks.upyun.protocol') === 'https' ? true : false;
        return new Upyun($config);
    }


    public function write($path, $contents, Config $config)
    {
        // TODO: Implement write() method.

        return $this->client()->write($path, $contents);
    }

    public function writeStream($path, $resource, Config $config)
    {
        // TODO: Implement writeStream() method.

        return $this->client()->write($path, $resource);
    }

    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.

        return $this->write($path, $contents, $config);
    }

    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.

        return $this->writeStream($path, $resource, $config);
    }

    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.

        $this->copy($path, $newpath);
        return $this->delete($path);
    }

    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.

        return $this->writeStream($newpath, fopen($this->getUrl($path), 'r'), new Config());;
    }

    public function delete($path)
    {
        // TODO: Implement delete() method.

        return $this->client()->delete($path);

    }

    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.

        return $this->client()->deleteDir($dirname);
    }

    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.

        return $this->client()->createDir($dirname);
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.

        return true;
    }

    public function has($path)
    {
        // TODO: Implement has() method.

        return $this->client()->has($path);
    }

    public function read($path)
    {
        // TODO: Implement read() method.

        $contents = file_get_contents($this->getUrl($path));
        return compact('contents', 'path');
    }

    public function readStream($path)
    {
        // TODO: Implement readStream() method.

        $stream = fopen($this->getUrl($path), 'r');
        return compact('stream', 'path');
    }

    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.

        $list = [];
        $result = $this->client()->read($directory, null, [ 'X-List-Limit' => 100, 'X-List-Iter' => null]);
        foreach ($result['files'] as $files) {
            $list[] = $this->normalizeFileInfo($files, $directory);
        }
        return $list;

    }

    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.

        return $this->client()->info($path);
    }

    public function getSize($path)
    {
        // TODO: Implement getSize() method.

        $response = $this->getMetadata($path);
        return ['size' => $response['x-upyun-file-size']];
    }

    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.

        $headers = get_headers($this->getUrl($path), 1);
        $mimetype = $headers['Content-Type'];
        return compact('mimetype');

    }

    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.

        $response = $this->getMetadata($path);
        return ['timestamp' => $response['x-upyun-file-date']];
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.

        return true;
    }


    public function getUrl($path)
    {
        return $this->normalizeHost($this->domain).$path;
    }


    protected function normalizeFileInfo(array $stats, string $directory)
    {
        $filePath = ltrim($directory . '/' . $stats['name'], '/');
        return [
            'type' => $this->getType($filePath)['type'],
            'path' => $filePath,
            'timestamp' => $stats['time'],
            'size' => $stats['size'],
        ];
    }

    protected function normalizeHost($domain)
    {
        if (0 !== stripos($domain, 'https://') && 0 !== stripos($domain, 'http://')) {
            $domain = $this->protocol."://{$domain}";
        }
        return rtrim($domain, '/').'/';
    }
}