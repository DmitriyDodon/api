<?php

namespace Database\Seeders;

use App\Models\Continent;
use App\Models\Country;
use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // continent seeder

        $rawContinents = Http::get('http://country.io/continent.json')->json();

        $continents = array_unique($rawContinents);

        $data = [];

        foreach ($continents as $key => $value) {
            $data[] = ['continent_code' => $value];
        }
        DB::table('continents')->insert($data);


        // country seeder

        $countries = Http::get('http://country.io/names.json')->json();

        $data = [];

        $continentFromDB = Continent::all();

        foreach ($countries as $country_code => $country_name) {
            $data[] = ['country_code' => $country_code
                , 'country_name' => $country_name
                , 'continent_id' => $continentFromDB->firstWhere('continent_code', $rawContinents[$country_code])->id
            ];
        }

        DB::table('countries')->insert($data);

        $countriesFromDB = Country::all()->pluck('id');

        // users seeder

        $countriesFromDB->each(function ($country) {
            if (rand(0, 2000) % 70 === 0) {
//                echo 'here' . PHP_EOL;
                \App\Models\User::factory(5)->create([
                    'country_id' => $country
                ]);
            }
        });
        \App\Models\User::factory(10)->create([
            'country_id' => $countriesFromDB->random()
        ]);


        $users = User::all();

        //labels seeder and project

        $users->each(function ($user) use ($users) {
            $labels = Label::factory(10)->create([
                'user_id' => $user->id
            ]);

            $projects = Project::factory(10)->create([
                'user_id' => $user->id
            ]);

            // attaching owners to linked list and attaching random users to projects
            $projects->each(function ($project) use ($user, $users) {
                $project->linkedUsers()->syncWithoutDetaching($user->id);
                $project->linkedUsers()->syncWithoutDetaching($users->random(mt_rand(3, 5))->pluck('id'));
            });
        });

        $labels = Label::all();

        $projects = Project::all();

        $projects->each(function ($project) use ($labels) {
            $project->labels()->syncWithoutDetaching($labels->random(mt_rand(0, 3))->pluck('id'));
        });


    }
}
