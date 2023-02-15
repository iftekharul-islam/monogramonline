<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Design;
use App\DesignSort;
use App\DesignLog;
use Monogram\ImageHelper;
use Monogram\Pendant;

class DesignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        if ($request->has('StyleName') && $request->get('StyleName') != '') {
          
          $design = Design::check($request->get('StyleName'));
          
          return redirect()->action('DesignController@edit', ['id' => $design->id]);
          
        } else {
          return redirect()->back()->withErrors('No Graphic SKU Provided');
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $design = Design::with('sort', 'child_skus')->find($id);
        
        if (!$design) {
          return redirect()->back()->withErrors('Design not found');
        }
        
        $file = Design::findTemplate($design->StyleName);
        
        if ($file) {
          $design->template = '1';
        } else {
          $design->template = '0';
        }
        
        $result = Pendant::findXmlSettings($design->StyleName);
        
        $design->xml = $result['found'];
        
        if ($result['found'] == '1') {
          $xml = $result['xml'];
        } 
        
        $design->save();
        
        $thumb = '/assets/images/template_thumbs/' . $design->StyleName . '.jpg';
        
        try {
          ImageHelper::createThumb($file, 0, base_path() . '/public_html' .  $thumb, 350);
        } catch (\Exception $e) {
          Log::error('Imagehelper createThumb: ' . $e->getMessage());
          $thumb = null;
        }
        
        $cases = [  'No Change' => 'No Change',
                    'UpperCase' => 'UpperCase',
                    'Title Case' => 'Title Case',
                    'Monogram' => 'Monogram',
                    'Monogram U Center' => 'Monogram U Center',
                    'Monogram LUL Center' => 'Monogram LUL Center',
                    'Monogram L Center' => 'Monogram L Center',
                    'LowerCase' => 'LowerCase'
                  ];
        
        $fonts = Design::getFonts(); 
        
        if (!$fonts) {
          return redirect()->back()->withInput()->withErrors('Font file could not be loaded');
        }
        
        return view('designs.show', compact('design', 'file', 'xml', 'thumb', 'cases', 'fonts'));
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
        if (!$request->has('StyleName')) {
          return redirect()->back()->withErrors('Style Name not set');
        }
        
        Design::saveTemplate($id, trim($request->get('StyleName')), $request->file('template'));
        
        DesignLog::add($id, 'Settings updated');
        
        if (Pendant::saveXmlSettings($request->all())) {
          return redirect()->action('DesignController@edit', ['id' => $id])
                            ->withSuccess('Graphic SKU settings updated');
        } else {
          return redirect()->back()->withInput()->withErrors('Settings could not be saved... Settings.xml may be in use');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
}
