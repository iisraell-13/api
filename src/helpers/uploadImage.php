<?php
namespace Src\Helpers;

class uploadImage {

    public function upload()
    {
        $response = array();
        if($_FILES['picture'] ) {
            $upload_dir = 'uploads/';
            $server_url = 'https://sandbox.api';
            $image_name = $_FILES["picture"]["name"];
            $image_tmp_name = $_FILES["picture"]["tmp_name"];
            $error = $_FILES["picture"]["error"];
            if(!$error) {
                $random_name = rand(1000,1000000)."-".$image_name;
                $upload_name = $upload_dir.strtolower($random_name);
                $upload_name = preg_replace('/\s+/', '-', $upload_name);
                if(move_uploaded_file($image_tmp_name , $upload_name)) {
                    return array(
                        "status" => "success",
                        "error" => false,
                        "message" => "File uploaded successfully",
                        "url" => $server_url."/".$upload_name
                    );
                }
            }
            return array(
                    "status" => "error",
                    "error" => true,
                    "message" => "Error uploading the file!"
                );
        }
        return $response;
    }
}