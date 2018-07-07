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

class SaveFileController
{
    public function saveFile(Request $request){
        $data = $request->get('content');
        $type = $request->get('type');
        $ip = $request->getClientIp();

        Storage::put(md5($data) . '.' . $type, $data($request) );

        return ( new Response( ['Message' => 'File has been saved', 'data' => md5($data)]  ) )
            ->header('Content-Type', 'application/json' );
    }

}