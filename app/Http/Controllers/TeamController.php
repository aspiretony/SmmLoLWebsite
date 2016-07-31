<?php

namespace App\Http\Controllers;

use App\Contestant;
use App\Team;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function register(Request $request)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:72',
            'tag' => 'required|min:3|max:6',
            'password' => 'required|min:4|max:24',
            // TODO: More validation (contestant details)
        ]);
        if ($validator->fails())
            return response()->json([
                'result' => 'invalid',
                'errorBag' => $validator->errors()->toArray()
            ]);
        // TODO: Database transaction
        // Create team
        $team = new Team;
        $team->name = $request->input('name');
        $team->tag = $request->input('tag');
        $team->password = Hash::make($request->input('password'));
        try {
            $team->save();
        } catch (QueryException $e) {
            return $this->failedResponse('Unable to create team');
        }
        // Create all contestants
        try {
            foreach ($request->input('contestants') as $contestant) {
                $c = new Contestant;
                $c->first_name = $contestant['first_name'];
                $c->last_name = $contestant['last_name'];
                $c->batch = $contestant['batch'];
                $c->summoner_name = $contestant['summoner_name'];
                $c->phone = $contestant['phone'];
                $c->facebook = $contestant['facebook'];
                $c->team_id = $team->id;
                $c->save();
            }
        } catch (QueryException $e) {
            return $this->failedResponse('Unable to create contestant');
        }
        return response()->json(['result' => 'success']);
    }

    private function failedResponse($msg = 'No additional information')
    {
        return response()->json([
            'result' => 'failed',
            'message' => 'Exception: ' . $msg
        ]);
    }
}