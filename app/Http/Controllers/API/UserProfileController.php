<?php

namespace App\Http\Controllers\API;
       
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\UserProfile;
use Validator;
use App\Http\Resources\UserProfile as UserProfileResource;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends BaseController
{

     /**
     * Update UserProfile resource in storage.
     *
     * Update total logins and last login time after user loggin
     * @param  \Illuminate\Http\Request  $request
     * @return Boolean
     */
    public static function update()
    {
        try {
            $user = Auth::user();
            $userProfile = $user->profile;
            if (is_null($userProfile)) {
                $userProfile = new UserProfile();
                $userProfile->total_logins = 1;
                $userProfile->last_login_at = date('Y-m-d h:i:s');
                $user->profile()->save($userProfile);
                return true;
            }

            $userProfile->total_logins += 1;
            $userProfile->last_login_at = date('Y-m-d h:i:s');
            $userProfile->save();
            return true;

        } catch ( \Exception $e ) {
            return false;   
        }
    } 
    
    /**
     * Display the UserProfile resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {       
        try{
            $user = Auth::user();
            $userProfile = $user->profile;
        
            if (is_null($userProfile)) {
                return $this->sendError('User profile not found.');
            }
           
            return $this->sendResponse((new UserProfileResource($userProfile))->additional(['meta' => ['email' => $user->email]]), 'User profile retrieved successfully.');
       
        } catch ( \Exception $e ) {
            return $this->sendError('Internal server error.', ['info' => 'Oops something went wrong. Keep breathing!']);   
        }
    }
    
}
