<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChildHierarchy extends Controller
{

    public function townsByDepartment ($id) {
        $ol = env('OPEN_LOCATION_DB');
        $towns = DB::table($ol.'.towns')
        ->join($ol.'.headquarters',$ol.'.headquarters.town_id',$ol.'.towns.id')
        ->join('game_users','game_users.headquarter_id',$ol.'.headquarters.id')
        ->leftJoin('game_user_records','game_user_records.game_user_id','game_users.id')
        ->leftJoinSub($this->validMinigames(),'valid_mini_games',function($join){
            $join->on('game_user_records.mini_game_id','valid_mini_games.id')
                ->on('valid_mini_games.grade_id','game_users.grade_id');
        })
        ->where($ol.'.towns.department_id',$id)
        ->groupBy($ol.'.towns.id')
        ->select($ol.'.towns.id',$ol.'.towns.name', DB::raw('COUNT(valid_mini_games.id) as total_played'))
        ->orderBy($ol.'.towns.name')
        ->get();
        return $towns;
    }

    public function institutionsByTown ($id) {
        $ol = env('OPEN_LOCATION_DB');

        $institutions = DB::table($ol.'.institutions')
        ->join($ol.'.headquarters',$ol.'.headquarters.institution_id',$ol.'.institutions.id')
        ->join('game_users','game_users.headquarter_id',$ol.'.headquarters.id')
        ->leftJoin('game_user_records','game_user_records.game_user_id','game_users.id')
        ->leftJoinSub($this->validMinigames(),'valid_mini_games',function($join){
            $join->on('game_user_records.mini_game_id','valid_mini_games.id')
                ->on('valid_mini_games.grade_id','game_users.grade_id');
        })
        ->where($ol.'.headquarters.town_id',$id)
        ->groupBy($ol.'.institutions.id')
        ->select($ol.'.institutions.id',$ol.'.institutions.name', DB::raw('COUNT(valid_mini_games.id) as total_played'))
        ->orderBy($ol.'.institutions.name')
        ->get();
        return $institutions;
    }
    
    public function headquartersByInstitutions ($id){
        $ol = env('OPEN_LOCATION_DB');
        // $headquarters = json_decode(file_get_contents($open_location.'api/headquartersByInstitution/'.$id), true);
        $headquarters = DB::table($ol.'.headquarters')
        ->join('game_users','game_users.headquarter_id',$ol.'.headquarters.id')
        ->leftJoin('game_user_records','game_user_records.game_user_id','game_users.id')
        ->leftJoinSub($this->validMiniGames(),'valid_mini_games',function($join){
            $join->on('game_user_records.mini_game_id','valid_mini_games.id')
                ->on('valid_mini_games.grade_id','game_users.grade_id');
        })
        ->where($ol.'.headquarters.institution_id',$id)
        ->groupBy($ol.'.headquarters.id')
        ->select($ol.'.headquarters.id',$ol.'.headquarters.name', DB::raw('COUNT(valid_mini_games.id) as total_played'))
        ->orderBy($ol.'.headquarters.name')
        ->get();
        return $headquarters;
    }
    
    public function groupsByHeadquarters ($id) {
        $groups = DB::table('game_users')
        ->join('grades', 'game_users.grade_id', 'grades.id')
        ->leftJoin('game_user_records','game_user_records.game_user_id','game_users.id')
        ->leftJoinSub($this->validMiniGames(),'valid_mini_games',function($join){
            $join->on('game_user_records.mini_game_id','valid_mini_games.id')
                ->on('valid_mini_games.grade_id','game_users.grade_id');
        })
        ->select('grades.id', 'grades.name',DB::raw('COUNT(valid_mini_games.id) as total_played'))
        ->groupBy('grades.id')
        ->where('headquarter_id',$id)
        //->distinct()
        ->get();
        return $groups;
    }

    public function studentsByGroups ($id1, $id2) {
        $students = DB::table('game_users')
        ->join('grades', 'game_users.grade_id', 'grades.id')
        ->leftJoin('game_user_records','game_user_records.game_user_id','game_users.id')
        ->select('game_users.id', DB::raw('CONCAT(game_users.first_name," ", game_users.second_name, " ", game_users.first_surname, " ", game_users.second_surname) as full_name'),DB::raw('COUNT(game_user_records.id) as total_played'))
        ->where([
            ['headquarter_id', $id1],
            ['grades.id', $id2]
        ])
        ->groupBy('game_users.id')
        //->distinct()
        ->orderBy('full_name', 'asc')
        ->get();
        return $students;
    }
    public function validMinigames(){
	 return DB::table('mini_games')
		 ->join('subject_mini_game','subject_mini_game.mini_game_id','mini_games.id')
		 ->select('mini_games.*');
    }
}
