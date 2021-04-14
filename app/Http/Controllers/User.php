<?php

namespace App\Http\Controllers;

use App\Jobs\AddingUsers;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class User extends Controller
{
    public function addUsers(Request $request)
    {
        $data = $request->validate([
            '*.name' => "required|max:255",
            '*.email' => "required|email|unique:App\Models\User|max:255",
            '*.country_code' => "required|max:2|min:2",
        ]);

        $request['device_name'] = 'web';

        $countries = Country::all()->pluck('id', 'country_code');

        $user_data = [];

        $users = [];

        foreach ($data as $user) {
            $user['country_id'] = $countries[strtoupper($user['country_code'])];
            $user['verification_token'] = uniqid();
            unset($user['country_code']);
            $users[] = $token = \App\Models\User::create($user);
            $token = $token->createToken($request->device_name)->plainTextToken;
            $user_data[] = ['user' => $user['email'], 'token' => $token];

        }

        AddingUsers::dispatch($user_data);

        return response()->json(['status' => 'Ok', 'message' => 'Users added to the queue' , 'Users' => $users]);
    }

    public function verify(Request $request , \App\Models\User $user)
    {
        if ($user->verification_token == $request->token){
            $user->verified = 1;
            $user->update();
            return response()->json(['status' => 'Ok', 'message' => 'User verified']);
        }

        return response()->json(['status' => 'Bad', 'message' => 'User not verified']);

    }

    public function listUsers(Request $request)
    {
        $query = DB::table('users')->select('users.*');

        if ($request->has('name')){
            $query->where('name' , '=' , $request->get('name'));
        }
        if ($request->has('email')){
            $query->where('email' , '=' , $request->get('email'));
        }

        if ($request->has('is_verified')){
            $query->where('verified' , '=' , 1);
        }

        if ($request->has('country_code')){
            $query->join('countries' , 'users.country_id' , '=' , 'countries.id')
                ->where('countries.country_code' , '=' , mb_strtoupper($request->get('country_code')));
        }

        $result = $query->get();


        return response()->json(['status' => 'Ok', 'users' => $result]);
    }

    public function editUsers(Request $request)
    {
        $data = $request->validate([
            '*.id' => 'required',
            '*.name' => "required|max:255",
            '*.email' => "required|email|max:255|distinct",
            '*.country_code' => "required|max:2|min:2"
        ]);

        $countries = Country::all()->pluck('id' , 'country_code');

        foreach ($data as $user_data){
            $country_id = $countries[strtoupper($user_data['country_code'])];
            $user = \App\Models\User::find($user_data['id']);
            $user->update(['name' => $user_data['name'], 'email' => $user_data['email'] , 'country_id' => $country_id]);
        }
    }

    public function deleteUsers(Request $request)
    {
        $data = [];
        foreach ($request->json() as $id) {
            $data[] = $id;
        }
        \App\Models\User::whereIn( 'id' , $data )->delete();
    }

}
