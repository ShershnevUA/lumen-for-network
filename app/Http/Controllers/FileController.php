<?php
/**
 * Created by PhpStorm.
 * User: user13
 * Date: 07.07.18
 * Time: 14:28
 */

namespace App\Http\Controllers;


use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function uploadFile( Request $request )
    {
        $file = $request->file('file' );
        $originalName = $file->getClientOriginalName();

        $this->writeNetworkLog( 'Upload start for file ' . $originalName );
        $mimeType = $file->getClientMimeType();
        $fileData =  $this->encryptFile( file_get_contents($file) );
        $this->writeNetworkLog( 'File ' . $originalName . ' was encrypted' );

        $file = json_decode( Storage::get('network.json') );
        $storage = $file[0];
        $client = new \GuzzleHttp\Client();
        $this->writeNetworkLog( 'Send file ' . $originalName . ' to storage' );

        $response = $client->request('POST','http://' . $storage . '/save-file', ['form_params' => ['data' => $fileData]] );
        $response = $response->getBody();
        $this->writeNetworkLog( 'File ' . $originalName . ' has been sent to storage.' );

        $this->writeFileInfo( [
            'originalName'  => $originalName,
            'mimeType'      => $mimeType,
            'storage'       => $storage,
            'storageId'     => json_decode($response->getContents(),true )['data']
        ]);

        return ( new Response( $response ) )
            ->header('Content-Type', 'application/json' );
    }

    public function getMyFile( Request $request )
    {
        $storageId = $request->query('storage_id');
        $file = $this->findFileInfo( $storageId );

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $file['storage'] . '/get-file?storage_id=' . $storageId);
        if( 200 === $response->getStatusCode()){
            $this->writeNetworkLog( 'File ' . $storageId . ' was found. Start decrypt' );
            $fileContent = json_decode($response->getBody()->getContents(),true )['data'];
            return ( new Response( [ 'file_content' => $this->decryptFile($fileContent) ] ) );
        }else{
            return ( new Response( $response ) )
                ->header('Content-Type', 'application/json' );
        }
    }

    private function encryptFile($file)
    {
        if ( !Storage::exists( 'public.pem' ) ){
            $publicKey = $this->generateKeys();
        }else {
            $publicKey = Storage::get( 'public.pem' );
        }
        openssl_public_encrypt( $file, $encryptedFile, $publicKey );

        return base64_encode( $encryptedFile );
    }

    private function decryptFile( $file )
    {
        if ( !Storage::exists( 'private.pem' ) ){
            return false;
        }else {
            $publicKey = Storage::get( 'private.pem' );
        }
        openssl_private_decrypt( base64_decode( $file ), $decryptedFile, $publicKey );

        return $decryptedFile;
    }

    private function generateKeys()
    {
        $config = array(
            'digest_alg'        => 'sha512',
            'private_key_bits'  => 2048,
            'private_key_type'  => OPENSSL_KEYTYPE_RSA,
        );

        $result = openssl_pkey_new( $config );
        openssl_pkey_export( $result, $privateKey );
        $publicKey = openssl_pkey_get_details( $result );
        $publicKey = $publicKey['key'];

        Storage::put( 'public.pem', $publicKey );
        Storage::put( 'private.pem', $privateKey );

        return $publicKey;
    }
}