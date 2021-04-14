<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Project extends Controller
{
    public function addProjects(Request $request, \App\Models\User $user)
    {
        $data = $request->validate([
            '*.name' => 'required'
        ]);

        $projects = [];

        foreach ($data as $project) {
            $projects[] = \App\Models\Project::create(['user_id' => $user->id, 'name' => $project['name']])->id;
        }


        $user->projects()->attach($projects);
    }

    public function linkUser(Request $request)
    {
        $data = $request->validate([
            '*.project_id' => 'required|exists:App\Models\Project,id',
            '*.users' => 'required|exists:App\Models\User,id'
        ]);

        foreach ($data as $project) {
            $temp = \App\Models\Project::find($project['project_id']);
            $temp->linkedUsers()->attach($project['users']);
        }
    }

    public function listProjects(Request $request)
    {
        $query = DB::table('projects')->select('projects.*');

        if ($request->has('email') && $request->get('email') !== null) {
            $query->join('users', 'projects.user_id', '=', 'users.id')
                ->where('users.email', '=', $request->get('email'));
        }

        if ($request->has('labels') && $request->get('labels') !== null) {
            $query->join('label_project', 'projects.id', '=', 'label_project.project_id')
                ->join('labels', 'label_project.label_id', '=', 'labels.id')
                ->whereIn('labels.id', $request->get('labels'));
        }

        if ($request->has('continent') && $request->get('continent') !== null) {
            if (!$request->has('email') || $request->get('email') === null) {
                $query->join('users', 'projects.user_id', '=', 'users.id');
            }
            $query->join('countries', 'users.country_id', '=', 'countries.id')
                ->join('continents', 'countries.continent_id', '=', 'continents.id')
                ->where('continents.id', '=', $request->get('continent'));
        }

        $filtered_data = $query->get()->unique();


        $result = collect();
        $user_projects = Auth::user()->projects->pluck('id');

        foreach ($filtered_data as $project){
            if ($user_projects->contains($project->id)){
                $result->add($project);
            }
        }


        return $result;
    }

    public function deleteProject(Request $request)
    {
        $data = $request->validate([
            'projects' => 'required|exists:App\Models\Project,id'
        ]);

        foreach ($data['projects'] as $project_id){
           $project = \App\Models\Project::find($project_id);
           if ($project->user_id === Auth::id()){
               $project->delete();
           }
        }

    }
}
