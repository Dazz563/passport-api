<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => true
        ], 400));
    }

    // overiding the attributes names
    // messages above will be sufficient
    // public function attributes()
    // {
    //     return [
    //         'email' => 'email address',
    //         'password' => 'abracadabra',
    //     ];
    // }


    // overiding the attributes messages
    // public function messages()
    // {
    //     return [
    //         '*.required' => 'This field is required',
    //         'password.min' => 'too short ma bra',
    //         'password.max' => 'too long ma bra',
    //     ];
    // }
}

// THIS CAN BE WRITTEN DIRECTLY IN YOUR CONTROLLER
// $login = Validator::make($req->all(), [
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // if ($login->fails()) {
        //     return response()->json(['error' => $login->errors()->all()]);
        // }
