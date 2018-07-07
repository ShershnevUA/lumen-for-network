<?php
/**
 * Created by PhpStorm.
 * User: user13
 * Date: 07.07.18
 * Time: 14:28
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController
{
    public function uploadFile(Request $request){
        $fileData =  $this->encryptFile(file_get_contents($request->file('file')));
        $file = json_decode(Storage::get('network.json'));
        var_dump('http://' . $file[0]);
        $client = new \GuzzleHttp\Client();

        $response = $client->request('PUT','http://' . $file[0] . '/save-file', ['body' => ['data' => $fileData],]);
        return ( new Response( $response->getBody() ) )
            ->header('Content-Type', 'application/json' );
    }

    private function encryptFile($file){
        if (!Storage::exists('public.pem')){
            $publicKey = $this->generateKeys();
        }else {
            $publicKey = Storage::get('public.pem');
        }
        openssl_public_encrypt($file, $encryptedFile, $publicKey);

        return base64_encode($encryptedFile);
    }

    private function decryptFile($file){
        if (!Storage::exists('private.pem')){
            return false;
        }else {
            $publicKey = Storage::get('private.pem');
        }
        openssl_private_decrypt(base64_decode($file), $decryptedFile, $publicKey);

        return $decryptedFile;
    }

    private function generateKeys(){
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $result = openssl_pkey_new( $config );
        openssl_pkey_export( $result, $privateKey );
        $publicKey = openssl_pkey_get_details( $result );
        $publicKey = $publicKey["key"];

        Storage::put('public.pem', $publicKey );
        Storage::put('private.pem', $privateKey );

        return $publicKey;
    }
}