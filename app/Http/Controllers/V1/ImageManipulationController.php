<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Requests\UpdateImageManipulationRequest;
use App\Models\ImageManipulation;
use App\Models\Album;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use App\Http\Resources\V1\ImageManipulationResource;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return ImageManipulationResource::collection(ImageManipulation::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageManipulationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
        //
        $all = $request->all();
        
        $image = $all['image'];
        unset($all['image']);
        $data = [
        	'type' => ImageManipulation::TYPE_RESIZE,
        	'data' => json_encode($all),
        	'user_id' => null
        ];
        
        if( isset($all['album_id']) )
        {
        	$data['album_id'] = $all['album_id'];
        }
        
        $dir = 'images/' . Str::random() . '/';
        $absolutePath = public_path($dir);
        Storage::makeDirectory($absolutePath);
        
        if($image instanceof UploadedFile)
        {
        	$data['name'] = $image->getClientOriginalName();
        	//test.jpg -> test-resized.jpg
        	
        	$filename = pathinfo($data['name'], PATHINFO_FILENAME);
        	$extension = $image->getClientOriginalExtension();
        	
        	$originalPath = $absolutePath . $data['name'];
        	
        	$image->move($absolutePath, $data['name']);
        }
        
        else
        {
        	$data['name'] = pathinfo($image, PATHINFO_BASENAME);
        	$filename = pathinfo($image, PATHINFO_FILENAME);
        	$extension = pathinfo($image, PATHINFO_EXTENSION);
        	$originalPath = $absolutePath . $data['name'];
        	
        	copy($image, $originalPath );
        }
        
        $data['path'] = $dir . $data['name'];
        
        $w = $all['w'];
        $h = $all['h'] ?? false;
        
        list($width, $height, $image) = $this->getImageWidthAndHeight($w, $h, $originalPath);
        
        $resizedFilename = $filename . '-resized.' . $extension;
        
        $image->resize($width, $height)->save($absolutePath.$resizedFilename);
        
        $imageManipulation = ImageManipulation::create($data);
        
		return new ImageManipulationResource($imageManipulation);
    }
    
    public function byAlbum(Album $album)
    {
        $where = [
        	'album_id' => $album->id
        ];
        
        return ImageManipulationResource::collection(ImageManipulation::where($where)->paginate() );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $image)
    {
        //
        return new ImageManipulationResource($image);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateImageManipulationRequest  $request
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateImageManipulationRequest $request, ImageManipulation $imageManipulation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $image)
    {
        //
        $image->delete();
        return response('', 204);
    }
    
    protected function getImageWidthAndHeight($w, $h, string $originalPath)
    {
    	//1000 - 50% => 500px
    	$image = Image::make($originalPath);
    	$originalWidth = $image->width();
    	$originalHeight = $image->height();
    	
    	if( str_ends_with($w, '%') )
    	{
    		$ratioW = (float)str_replace('%', '', $w);
    		$ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;
    	}
    	
    	else
    	{
    		$newWidth = (float)$w;
    		/**
    		* $originalWidth - $newWidth
    		* $originalHeight - $newHeight
    		* ----------------------------
    		* $newHeight = $originalHeight * $newWidth/$originalWidth
    		*/
    		$newHeight = $h ? (float)$h : $originalHeight * $newWidth/$originalWidth;
    		
    	}
    	
    	return [$newWidth, $newHeight, $image];
    }
}
