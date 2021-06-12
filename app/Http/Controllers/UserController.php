<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    private $userDetails;
    public function __construct(UserService $userService)
    {
        $this->userDetails = $userService;
    }
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            } else {
                $user = User::where(['email' => $request->email])->first();
                $user->update([
                    'api_token' => $token,
                ]);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['message' => 'login succesful', compact('token')]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'user_type' => $request->get('user_type'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    public function getAuthenticatedUser()
    {

        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    public function updateCardDetails()
    {

    }
    public function updateProfileInfo(User $user, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'dob' => 'required|string|dob|max:255|unique:users',
            'card_number' => 'required|string|card_number|max:255|unique:users',
            'cvv' => 'required|string|cvv|max:255|unique:users',
            'expiry_date' => 'required|string|expiry_date|max:255|unique:users',
            'card_pin' => 'required|string|card_pin|max:255|unique:users',
        ]);

        $this->userDetails->updateUser($user, $request->all());

    }

    public function updatePhoto(Request $request)
    {
        $this->validate($request, [
            'photo' => 'image|max:3999',
        ]);

        if ($request->hasFile('photo')) {
            $gatewayIcon = time() . 'photo' . $request->file('photo')->getClientOriginalName();
            $photo = str_replace(" ", "_", $gatewayIcon);
            $path = $request->file('photo')->storeAs('public/images', $photo);
        }
        $photo = ($request->hasFile('photo')) ? url('/') . '/storage/images/' . $photo : null;
        $getUser = User::where('id', Auth::user()->id)->first();
        $getUser->update([
            'photo' => $photo,
        ]);
        return response()->json(['message' => 'Profile Picture Successfully Updated'], 200);
    }
}
