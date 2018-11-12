<?php
/**
 * Created by PhpStorm.
 * User: lzning
 * Date: 2018/11/12
 * Time: 2:10 PM
 */

namespace Zning\LaravelUpYun\plugins;


use League\Flysystem\Plugin\AbstractPlugin;

class ImagePreviewUrl extends AbstractPlugin
{

    public function getMethod()
    {
        return 'getUrl';
    }
    public function handle($path = null)
    {
        return $this->filesystem->getAdapter()->getUrl($path);
    }

}