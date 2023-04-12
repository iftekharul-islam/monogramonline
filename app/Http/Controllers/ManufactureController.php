<?php

namespace App\Http\Controllers;

use App\Manufacture;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ManufactureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = Manufacture::orderBy('created_at', 'asc')->get();

        return view ( 'manufacturers.index', compact ( 'data' ) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Manufacture::create($request->all());

        return redirect()->action('ManufactureController@index')->withSuccess('Manufacture is successfully added');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $manufacture = Manufacture::findOrFail($id);
        $manufacture->name = $request->name;
        $manufacture->description = $request->description;
        $manufacture->save();

        return redirect()->action('ManufactureController@index')->withSuccess('Manufacture is successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $manufacture = Manufacture::findOrFail($id);
        $manufacture->delete();

        return redirect()->action('ManufactureController@index')->withSuccess('Manufacture is successfully deleted');
    }

    public function access($id)
    {
        $manufacture = Manufacture::findorFail($id);
        $users = User::where('is_deleted', 0)->get();

        return view('manufacturers.permission', compact('users', 'manufacture'));
    }

    public function accessUpdate(Request $request, $id)
    {
        $manufacture = Manufacture::findOrFail($id);

        $users = count($request->permit_manufactures) ? $request->permit_manufactures : [];

        foreach($users as $id){
            $user = User::find($id);
            if(!in_array($manufacture->id, $user->permit_manufactures)){
                $data = $user->permit_manufactures;
                $data[] = $manufacture->id;
                $user->permit_manufactures = $data;
                $user->save();
            }

        }
        return redirect()->action('ManufactureController@index')->withSuccess('Manufacture is successfully added');

    }
}
