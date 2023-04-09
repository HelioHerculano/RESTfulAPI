<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return $this->showAll($users);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $roles = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6!confirmed',
        ];

        $validator = Validator::make($request->all(),$roles);

        $data = $request->all(); 
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        return $this->showOne($user,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $roles = [
            'email' => 'email|unique:users,email,'. $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
        ];

        if($request->has('name')){
            $user->name = $request->name;
        }        

        if($request->has('email') && $request->email != $user->email){
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        if($request->has('admin')){
            if(!$user->isVerified()){
                return $this->errorResponse('Only verified users can modify the field',409);
            }
            $user->admin = $request->admin;
        }

        if(!$user->isDirty()){
            return $this->errorResponse('You need to specify a different value to update',422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->showOne($user);
    }
}
