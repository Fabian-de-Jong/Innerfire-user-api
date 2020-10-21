<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Controllers\API\UserProfileController as UserProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Log;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        try{
            $user = User::create($input);
        } catch ( \Exception $e ) {
            if ($e->getCode() == 23000) {              
                return $this->sendError('Dublicate entry.', ['info' => 'Your emailaddress is allready registerd.']);
            }
            return $this->sendError('Internal server error.', ['info' => 'Oops something went wrong. Keep breathing!']);   
        }

        $success['token'] =  $user->createToken('InnerFireUserApi')->accessToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User successfully registered.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            try{
                $user = Auth::user(); 
                $success['token'] =  $user->createToken('InnerFireUserApi')->accessToken; 
                $success['name'] =  $user->name;

                $profileAutoUpdate = UserProfileController::update();
                if ( !$profileAutoUpdate ) {
                    Log::error('Auto update of UserProfile id:' . $user->profile->id . ' failed.' );
                } 
            } catch ( \Exception $e ) {
                return $this->sendError('Internal server error.', ['info' => 'Oops something went wrong. Keep breathing!']);   
            }
            return $this->sendResponse($success, 'User logged in successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.');
        } 
    }
}