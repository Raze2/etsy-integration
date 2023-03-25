<?php

namespace App\Http\Controllers;

use App\Services\Etsy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EtsyController extends Controller
{
    public function index(Request $request){
        if($request->has("url") && $url = $request->get("url")){
            $data = (new Etsy())->get($url);
           
        }
        
        return Inertia::render('Etsy/Index', [
            'etsy_exsists' => auth()->user()->etsy_access_token ? true : false,
            'data' => $data ?? null,
        ]);
    }

    public function connectToEtsy(){
        return (new Etsy())->connect();
    }

    public function callback(Request $request){
        if($request->has("code")){
            
            $code = $request->get("code");
            $state = $request->get("state");
            
            (new Etsy())->checkCallback($code, $state);

            session()->flash("success", "Etsy connected successfully");
            return redirect()->route('etsy.index');
        }
        throw new \Exception("Invalid request");
    }
}
