<?php 

namespace App\Helpers;

class Api 
{ 
    public static function apiSuccessResponse($message,$data=NULL)
    {
        $success = [
            'status'       => true,
            'code'          => 200,
            'message'       => $message, 
            'data'          => $data
        ];

        return $success;
    }

    public static function validationResponse($validator){
        foreach($validator->errors()->toArray() as $v => $a){
            $validationError = [
                'status'       => false,
                'code'          => 422,
                'message'       => $a[0],
            ];
    
            return $validationError;
            
        }

    }

    public static function apiErrorResponse($message,$code=422)
    {
        $error = [
            'status'       => false,
            'code'          => $code,
            'message'       => $message,
        ];

        return $error;
    }
}