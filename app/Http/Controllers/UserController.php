<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\User;

class UserController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the authenticated User.
     *
     * @return Response
     */
    public function profile(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::user()->id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            $user->update();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'UPDATED'], 201);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Update Failed!'], 409);
        }
    }

    /**
     * Get all User.
     *
     * @return Response
     */
    public function allUsers()
    {
        return response()->json(['users' =>  User::all()], 200);
    }

    /**
     * Get one user.
     *
     * @return Response
     */
    public function singleUser($id)
    {
        try {
            User::destroy($id);

            return response()->json(['message' => 'User ID ' . $id . ' Deleted'], 200);
        } catch (\Exception $e) {

            return response()->json(['message' => 'user not found!'], 404);
        }
    }
}
