<?php
/**
 * Created by PhpStorm.
 * User: lzning
 * Date: 2018/11/12
 * Time: 1:59 PM
 */

namespace Zning\LaravelUpYun;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Zning\LaravelUpYun\plugins\ImagePreviewUrl;

class UpyunServiceProvider extends ServiceProvider
{


    public function boot()
    {

        Storage::extend('upyun', function ($app, $config) {
            $adapter = new UpyunAdapter(
                $config['serverName']
                , $config['operatorName']
                ,$config['operatorPassword']
                ,$config['domain']
                ,$config['protocol']
            );
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new ImagePreviewUrl());
            return $filesystem;
        });

    }


    public function register()
    {

    }

}