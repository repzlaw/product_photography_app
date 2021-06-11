<?php

namespace App\Http\Controllers;

use App\Models\Photograph;
use Illuminate\Http\Request;
use App\Models\PhotographRequest;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class PhotographyController extends Controller
{
    //function to handle product owners request
    public function productOwnerRequest(Request $request)
    {
        $this->validate($request, [
            'product_name' => 'required',
        ]);

        PhotographRequest::create([
            'product_owner_id' => Auth::user()->id,
            'product_name' => $request->input('product_name'),
            'facility_name' => 'talent ql facilties',
        ]);

        return response()->json(['message'=>'Photograph request sent successfully']);
    }

    //function to handle photographers viewing all requests
    public function ViewRequests(Request $request)
    {
        if (Auth::user()->user_type == 'photographer') {
            $request = PhotographRequest::all();
            return response()->json(['requests'=>$request]);

        }else{
            return response()->json(['message'=>'You cannot access this page because of your account type, use a photographers account to access this page']);
        }
    }

    //function to handle photographers viewing one request
    public function ViewOneRequest(Request $request, $id)
    {
        if (Auth::user()->user_type == 'photographer') {
            $request = PhotographRequest::where('id',$id)->first();
            return response()->json(['request'=>$request]);
        }else{
            return response()->json(['message'=>'You cannot access this page because of your account type, use a photographers account to access this page']);
        }
    }

    //function to handle photographers image upload
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'images' => 'required',
          ]);
  
          if ($request->hasfile('images')) {
              $images = $request->file('images');
  
              $pathUrls =[];
              
              foreach($images as $image) {
                  $name = $image->getClientOriginalName();
                  $path = $image->storeAs('uploads', $name, 'public');
                  $path = url('/').'/storage/uploads/'.$name;
                    array_push($pathUrls,$path);
                    ///handle thumbnails
                        $i = Image::make('storage/uploads/'.$name)->resize(100, 100);
                        $destinationPath = public_path('storage/thumbnails');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 666, true);
                        }
                        $i->resize(100, 100, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$name);
                }
                Photograph::create([
                    'photo_request_id' => $id,
                    'photographer_id' => Auth::user()->id,
                    'image' => json_encode($pathUrls)
                  ]);

            return response()->json(['message'=>'image(s) sent successfully']);
            
           }
           return response()->json(['message'=>'please select image(s) to upload']);
        
    }

    //function for product owner to view photos sent in by photographers
    public function productOwnerView($id)
    {
        $owner = Photograph::where('photo_request_id',$id)->first();
        $myArr = [];
        if ($owner) {
            $owner_id = PhotographRequest::where('id',$owner->photo_request_id)->first()->product_owner_id;
            //check if product owner is the one requesting access to the photos
            if ($owner_id == Auth::user()->id) {
                $photos = Photograph::where('id',$id)->get();
               
                $photoarr =[];
                foreach ($photos as $key => $ph) {
                    $myArr = [];
                    foreach (json_decode($ph->image) as $key => $img) {
                        $im =str_replace("uploads","thumbnails",$img);
                        array_push ($myArr,$im);
                    }
                    array_push ($photoarr,$myArr);
                }
                
                return response()->json(['message'=>'these are thumbnails of the photos taken. check them out','data'=>$photoarr]);
            } else {
                return response()->json(['message'=>'only the product owner can access this. login as the product owner']);

            }
            
        } else {
            return response()->json(['message'=>'no photographs taken yet. check back later']);
        }
        
    }

    //function that displays original photos if approved
    public function productOwnerDecide(Request $request, $id)
    {
        $owner = Photograph::where('id',$id)->first();
        if ($owner) {
            $owner_id = PhotographRequest::where('id',$owner->photo_request_id)->first()->product_owner_id;
            //check if product owner is the one requesting access to the photos
            if ($owner_id == Auth::user()->id) {
                $photos = Photograph::where('id',$id)->get();
               
                //check if the product owner approves the thumbnails
                if ($request->input('status')=='approve') {
                    $photoarr =[];
                    foreach ($photos as $key => $ph) {
                        $myArr = [];
                        foreach (json_decode($ph->image) as $key => $img) {
                            array_push ($myArr,$img);
                        }
                        array_push ($photoarr,$myArr);
                    
                    }
                    
                    return response()->json(['message'=>'approved sucessfully. these are the high resolution photos, enjoy','data'=>$photoarr]);

                } else if($request->input('status')=='disapprove') {
                    return response()->json(['message'=>'you disapproved this photographs']);
                }else{
                    return response()->json(['message'=>'invalid status passed. you can only approve or disapprove photographs']);
                }
                
                
            } else {
                return response()->json(['message'=>'only the product owner can access this. login as the product owner']);
            }
            
        } else {
            return response()->json(['message'=>'no photographs taken yet. check back later']);
        }
    }
}
