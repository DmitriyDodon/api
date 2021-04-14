<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Label extends Controller
{
    public function addLabels(Request $request, \App\Models\User $user)
    {
        $data = $request->validate([
            '*.name' => 'required|unique:App\Models\Label,name'
        ]);

        $labels = [];

        foreach ($data as $label) {
            $labels[] = ['name' => $label['name'], 'user_id' => $user->id];
        }

        return DB::table('labels')->insert($labels);

    }

    public function linkLabels(Request $request)
    {
        $data = $request->validate([
            '*.label_id' => 'required|exists:App\Models\Label,id',
            '*.projects' => 'required|exists:App\Models\Project,id'
        ]);

        foreach ($data as $label) {
            $temp_label = \App\Models\Label::find($label['label_id']);
            $temp_label->projects()->attach($label['projects']);
        }


    }

    public function filterLabels(Request $request)
    {
        $query = DB::table('labels')->select('labels.*');

        if ($request->has('email') && $request->get('email') !== null) {
            $query->join('users', 'labels.user_id', '=', 'users.id')
                ->where('users.email', '=', $request->get('email'));
        }
        if ($request->has('projects') && $request->get('projects') !== null) {
            $query->join('label_project', 'labels.id', '=', 'label_project.label_id')
                ->join('projects', 'label_project.project_id', '=', 'projects.id')
                ->whereIN('projects.id', $request->get('projects'));
        }

        $filtered_data = $query->get()->unique();


        $user_labels = Auth::user()->labels->pluck('id');



        $temp = [];

        $user_projects = Auth::user()->projects;

        foreach ($user_projects as $project){
            $temp[] = $project->labels->pluck('id');
        }

        $res = [];

        foreach($temp as $value){
            foreach ($value as $item){
                $res[] = $item;
            }
        }


        $result = collect();

        foreach ($filtered_data as $label){
            if ($user_labels->contains($label->id) || in_array($label->id , array_unique($res))){
                $result->add($label);
            }
        }


        return $result;



    }

    public function deleteLabels(Request $request)
    {
        $data = $request->validate([
            'labels' => 'required|exists:App\Models\Label,id'
        ]);

        foreach ($data['labels'] as $label_id) {
            $label = \App\Models\Label::find($label_id);
            if (Auth::id() === $label->user_id){
                $label->delete();
            }
        }
    }
}
