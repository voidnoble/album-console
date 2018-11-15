<?php

namespace App\Http\Controllers;

use App\Album;
use App\Http\Requests;
use App\Image;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ImagesController extends Controller
{
    private $albumUrl = "";
    private $imgDimensions = [];

    public function __construct()
    {
        $this->albumUrl = (env("APP_ENV") == "production")? "http://albums.motorgraph.com" : "http://albums.motorgraph.local";
        $this->imgDimensions = [360, 300];
    }

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
    public function create($id)
    {
        $album = Album::find($id);

        return view('albums.addImage')
            ->with('album', $album);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'album_id' => 'required|numeric|exists:albums,id',
            //'image' => 'required|image|mimes:jpeg,png,gif', // 단일 파일 업로드이면 주석 해제하고 사용
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return redirect()->route('add_image', ['id' => $request->get('album_id')])
                ->withErrors($validator)
                ->withInput();
        }

        $filePrefix = Carbon::today()->format('Ymdhis');
        $pathSuffix = '/'. substr($filePrefix, 0, 4) .'/'. substr($filePrefix, 4, 2) .'/'. substr($filePrefix, 6, 2);
        $destinationPath = 'data/albums'. $pathSuffix;
        $destinationThumbPath = 'data/albums'. $pathSuffix .'/thumb';
        if (!File::exists(public_path() .'/'. $destinationPath)) {
            File::makeDirectory(public_path() .'/'. $destinationPath, 0755, true);
        }
        if (!File::exists(public_path() .'/'. $destinationThumbPath)) {
            File::makeDirectory(public_path() .'/'. $destinationThumbPath, 0755, true);
        }

        $name = $request->get('name');

        $files = $request->file('images');
        $filesCount = count($files);
        $randomString_length = 8;
        $uploadCount = 0;
        foreach($files as $file) {
            $rules = [
                'file' => 'required|image|mimes:jpeg,png,gif'
            ];
            $validator = Validator::make(['file' => $file], $rules);

            if($validator->passes()) {
                $random_name = str_random($randomString_length);
                $extension = $file->getClientOriginalExtension();
                if ($name == "") {
                    $name = $file->getClientOriginalName();
                    $name = str_replace(".{$extension}", "", $name);
                }
                $filename = $filePrefix .'_'. $random_name .'_album_image.'. $extension;
                $uploadSuccess = $file->move($destinationPath, $filename);

                // 추가되는 이미지 순서번호 설정
                $order = Image::where('album_id', $request->get('album_id'))->max('order');
                $order = (isset($order))? ++$order : 0;
                // ORM 저장
                Image::create([
                    'name' => $name,
                    'description' => $request->get('description'),
                    'image' => $filename,
                    'album_id' => $request->get('album_id'),
                    'order' => $order,
                ]);

                // 썸네일 생성
                if (File::exists($destinationPath .'/'. $filename)) {
                    $srcPath = $destinationPath .'/'. $filename;
                    $thumbPath = $destinationThumbPath .'/'. $filename;

                    \Intervention\Image\Facades\Image::make($srcPath)->fit($this->imgDimensions[0], $this->imgDimensions[1])->save($thumbPath);
                }

                $uploadCount++;
            }
        }

        if ($uploadCount == $filesCount) {
            Session::flash('success', 'Upload successfully');
        }

        return redirect()->route('show_album', ['id' => $request->get('album_id')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $image = Image::find($id);
        $album = Album::find($image->album_id);

        return view('albums.editImage')
            ->with('image', $image)
            ->with('album', $album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(Request $request)
    {
        $id = $request->get('id');

        $image = Image::find($id);
        if (isset($image)) {
            $image->name = $request->get('name');
            $image->description = $request->get('description');
            $image->save();
        }

        return redirect()->route('show_album', ['id' => $image->album_id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $image = Image::find($id);

        $destinationPath = 'data/albums/';
        $success = File::delete(public_path() .'/'. $destinationPath. substr($image->image, 0, 4) .'/'. substr($image->image, 4, 2) .'/'. substr($image->image, 6, 2) .'/'. $image->image);

        if ($success) {
            $image->delete();
        } else {
            session()->flash('warning', '파일 삭제중 오류. 다시 시도하여주십시오.');
        }

        return redirect()->route('show_album', ['id' => $image->album_id]);
    }

    /**
     * Move a image between albums
     * @return string
     */
    public function move()
    {
        $rules = [
            'new_album' => 'required|numeric|exists:albums,id',
            'photo' => 'required|numeric|exists:images,id',
        ];

        $validator = Validator::make(request()->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('index');
        }

        // 해당 이미지의 앨범 아이디를 변경하여 앨범간 이동 처리 구현
        $image = Image::find(request()->get('photo'));
        $image->album_id = request()->get('new_album');
        $image->save();

        return redirect()->route('show_album',['id' => request()->get('new_album')]);
    }

    /**
     * Remove the specified resource from storage.
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroyMany()
    {
        $success = false;

        $ids = request()->get('ids');

        $rules = [
            'ids' => 'required'
        ];

        $validator = Validator::make(request()->all(), $rules);
        if($validator->fails()) {
            return response()->json(['success' => $success, 'reason' => '필수 값 누락!']);
        }

        $images = Image::whereIn('id', $ids)->get();

        foreach($images as $image) {
            $destinationPath = 'data/albums/';
            $success = File::delete(public_path() . '/' . $destinationPath . substr($image->image, 0, 4) .'/'. substr($image->image, 4, 2) .'/'. substr($image->image, 6, 2) . '/' . $image->image);

            if ($success) {
                $image->delete();
            } else {
                session()->flash('warning', '파일 삭제중 오류. 다시 시도하여주십시오.');
            }
        }

        return response()->json(['success' => $success]);
    }

    /**
     * Move a image between albums
     * @return string
     */
    public function moveMany()
    {
        $success = false;

        $ids = request()->get('ids');

        $rules = [
            'ids' => 'required'
        ];

        $validator = Validator::make(request()->all(), $rules);
        if($validator->fails()) {
            return response()->json(['success' => $success, 'reason' => '필수 값 누락!']);
        }

        $images = Image::whereIn('id', $ids)->get();

        foreach($images as $image) {
            /*$rules = [
                'new_album' => 'required|numeric|exists:albums,id',
                'photo' => 'required|numeric|exists:images,id',
            ];

            $validator = Validator::make(request()->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('index');
            }*/

            // 해당 이미지의 앨범 아이디를 변경하여 앨범간 이동 처리 구현
            $image->album_id = request()->get('new_album');
            $image->save();
        }

        return redirect()->route('show_album',['id' => request()->get('new_album')]);
    }

    /**
     * Sort images
     *
     * @param Request $request
     * @return Request
     */
    public function sort(Request $request)
    {
        /*
         * $request->get('album_id')
           $request->get('index') new index of the dragged element
           $request->get('oldindex') old index of the dragged element
         */
        $rules = [
            'album_id' => 'required|numeric',
            'index' => 'required|numeric',
            'oldindex' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'reason' => $validator]);
        }

        // drag&drop 대상 항목
        $where = [
            'album_id' => $request->get('album_id'),
            'order' => $request->get('oldindex'),
        ];
        $image = Image::where($where)->first();

        // 이동후의 항목이 있는지 검증
        if (!isset($image)) {
            return response()->json(['success' => false]);
        }

        // 정렬 처리
        if ($request->get('oldindex') > $request->get('index')) {
            Image::where('album_id', $request->get('album_id'))
                ->where(function($query) use($request) {
                    $index['old'] = $request->get('oldindex');
                    $index['new'] = $request->get('index');

                    $query->where('order', '>=', $index['new'])
                        ->where('order', '<', $index['old']);
                })
                ->increment('order');
        } elseif ($request->get('oldindex') < $request->get('index')) {
            Image::where('album_id', $request->get('album_id'))
                ->where(function($query) use($request) {
                    $index['old'] = $request->get('oldindex');
                    $index['new'] = $request->get('index');

                    $query->where('order', '>', $index['old'])
                        ->where('order', '<=', $index['new']);
                })
                ->decrement('order');
        }

        // 지정한(drop된) 순서 적용
        $image->order = $request->get('index');
        $image->save();
        unset($image);

        return response()->json(['success' => true]);
    }
}
