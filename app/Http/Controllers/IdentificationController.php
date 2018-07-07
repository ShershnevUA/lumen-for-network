<?php
/**
 * Created by PhpStorm.
 * User: user13
 * Date: 07.07.18
 * Time: 12:49
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class IdentificationController extends Controller
{
    public function getMyIp(){
        return ( new Response( ['my_ip' => $_SERVER['SERVER_ADDR']] ) )
            ->header('Content-Type', 'application/json' );
    }

    public function postNetworkIp( Request $request){
        $ip = $request->get('ip');
        if ( Storage::exists('network.json')){
            $file = json_decode(Storage::get('network.json'));
            if (!in_array( $ip, $file )){
                $file[] = $ip;
            }
        }else {
            $file = [$ip];
        }
        Storage::put('network.json', json_encode( $file, JSON_PRETTY_PRINT ));

        $message = 'Peers has been added';

        return ( new Response( ['Message' => $message] ) )
            ->header('Content-Type', 'application/json' );
    }

}