<?php
/**
 * Created by PhpStorm.
 * User: user13
 * Date: 07.07.18
 * Time: 15:22
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileStorageController extends Controller
{
    public function saveFile( Request $request ){
        $data = $request->get('data' );

        $ip = $request->getClientIp();
        Storage::put( md5( $data ) . '.' . md5( $ip ), $data );
        $this->writeNetworkLog( 'File ' . md5( $data ) . '.' . md5( $ip ) . ' has been sent to storage.' );
        return ( new Response([
            'Message' => 'File has been saved',
            'data' => md5( $data )
        ]))->header('Content-Type', 'application/json' );
    }

    public function getFile( Request $request ){
        $storageId = $request->query('storage_id');
        $ip = $request->getClientIp();
        $this->writeNetworkLog( $ip . ' want get file from storage.' );
        if (!Storage::exists( $storageId . '.' . md5( $ip ) ) ){
            $this->writeNetworkLog( 'Access denied for ' . $ip );
            return ( new Response([
                'Message' => 'Not found'
            ], 404 ))->header('Content-Type', 'application/json' );
        } else{
            $this->writeNetworkLog( 'Access allowed for ' . $ip );
            $file = Storage::get( $storageId . '.' . md5( $ip ) );

            return ( new Response([
                'Message' => 'File was found',
                'data' => $file
            ], 200))->header('Content-Type', 'application/json' );
        }
    }

}