<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function writeNetworkLog( $message )
    {
        $date = new \DateTime();
        $message = $date->format('Y-m-d H:i:s' ) . ' ' . $message;

        if ( !Storage::exists( 'network.log' )){
            Storage::put( 'network.log', $message . "\n" );
        }else {
            $file = Storage::get('network.log' );
            Storage::put( 'network.log', $file . $message . "\n" );
        }
    }

    protected function writeFileInfo( $fileInfo )
    {
        if (!Storage::exists( 'files.json' )){
            Storage::put( 'files.json', json_encode( [$fileInfo['storageId'] => $fileInfo], JSON_PRETTY_PRINT ) . "\n" );
        }else {
            $file = json_decode( Storage::get( 'files.json' ), true );
            $file[$fileInfo['storageId']] = $fileInfo;
            Storage::put( 'files.json', json_encode( $file, JSON_PRETTY_PRINT ) . "\n" );
        }
    }

    protected function findFileInfo( $storageId )
    {
        $file = json_decode( Storage::get('files.json'), true );
        return $file[$storageId];
    }
}
