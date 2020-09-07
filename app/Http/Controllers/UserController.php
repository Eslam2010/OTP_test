<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
//use Illuminate\Support\Facades\Validator;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request){
        Log::info($request);
        if(Auth::attempt(['mobile' => request('mobile'), 'password' => request('password')])){
            return view('home');
        }
        else{
            return Redirect::back ();
        }
    }

    public function loginWithOtp(Request $request){
        Log::info($request);
        $user  = User::where([['mobile','=',request('mobile')],['otp','=',request('otp')]])->first();
        if( $user){
            Auth::login($user, true);
            User::where('mobile','=',$request->mobile)->update(['otp' => null]);
            return \redirect('home');
        }
        else{
            return Redirect::back ()->withErrors('please check enter valid code/number');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:users|min:11',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ])->validate();
      /*  if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
          return $validator->errors();
        }*/
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        User::create($input);

        return redirect('login');
    }

    public function sendOtp(Request $request){

        $otp = rand(10000,99999);
        Log::info("otp = ".$otp);
        $user = User::where('mobile','=',$request->mobile)->update(['otp' => $otp]);
        // send otp to mobile no using sms api
        return response()->json([$user],200);
    }
}
