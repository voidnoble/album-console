<?php

namespace App\Http\Controllers;

use App\Album;
use App\AlbumArticlesRelationship;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AlbumsController extends Controller
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
        $perPage = 16;

        $albums = Album::with('Photos')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // 이미지 저장 경로
        $dataPath = 'data/albums';

        // 썸네일 생성 - 업로드시 생성하지만..
        foreach($albums as $album) {
            $filename = $album->cover_image;
            $destinationPath = public_path() .'/'. $dataPath .'/'. substr($filename, 0, 4) .'/'. substr($filename, 4, 2) .'/'. substr($filename, 6, 2);
            $destinationThumbPath = $destinationPath .'/thumb';

            if (!File::exists($destinationThumbPath . '/' . $filename)) {
                $srcPath = $destinationPath . '/' . $filename;
                $thumbPath = $destinationThumbPath . '/' . $filename;

                if (File::exists($srcPath)) {
                    Image::make($srcPath)->fit($this->imgDimensions[0], $this->imgDimensions[1])->save($thumbPath);
                }
            }
        }

        return view('albums.index')->with('albums', $albums);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('albums.createAlbum');
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
            'name' => 'required',
            'cover_image' => 'required|image',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('create_album_form')->withErrors($validator)->withInput();
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

        $file = $request->file('cover_image');
        $random_name = str_random(8);
        $extension = $file->getClientOriginalExtension();
        $filename = $filePrefix .'_'. $random_name .'_cover.'. $extension;
        $uploadSuccess = $request->file('cover_image')->move($destinationPath, $filename);

        // 썸네일 생성
        if (File::exists($destinationPath .'/'. $filename)) {
            $srcPath = $destinationPath .'/'. $filename;
            $thumbPath = $destinationThumbPath .'/'. $filename;

            Image::make($srcPath)->fit($this->imgDimensions[0], $this->imgDimensions[1])->save($thumbPath);
        }

        $album = Album::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'cover_image' => $filename,
            'published_at' => $request->get('published_at'),
        ]);

        // Add tags
        $album->tag(explode(',', $request->tags));

        return redirect()->route('show_album', ['id' => $album->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // 데이터 로딩
        $album = Album::with('Photos')->find($id);
        // URL 지정
        $album->url = $this->albumUrl;
        // 사용자정의 순서대로 정렬
        $album->Photos = $album->Photos->sortBy('order');

        // 이미지 저장 경로
        $dataPath = 'data/albums';

        // 썸네일 존재여부 확인 후 생성 - 업로드시 생성하지만 재검증
        foreach($album->Photos as $photo) {
            // 디렉토리 없으면 생성
            $pathSuffix = '/'. substr($photo->image, 0, 4) .'/'. substr($photo->image, 4, 2) .'/'. substr($photo->image, 6, 2);
            $destinationPath = $dataPath . $pathSuffix;
            $destinationThumbPath = $dataPath . $pathSuffix .'/thumb';
            if (!File::exists(public_path() .'/'. $destinationPath)) {
                File::makeDirectory(public_path() .'/'. $destinationPath, 0755, true);
            }
            if (!File::exists(public_path() .'/'. $destinationThumbPath)) {
                File::makeDirectory(public_path() .'/'. $destinationThumbPath, 0755, true);
            }

            if (!File::exists($destinationThumbPath . '/' . $photo->image)) {
                $srcPath = $destinationPath . '/' . $photo->image;
                $thumbPath = $destinationThumbPath . '/' . $photo->image;

                Image::make($srcPath)->fit($this->imgDimensions[0], $this->imgDimensions[1])->save($thumbPath);
            }
        }

        // 다른 화보로 이동을 위한 select option
        $albums = Album::with('Photos')->where('id', '<>', $id)->get();
        // 태그들
        $tags = $album->tags;

        return view('albums.album')->with('album', $album)->with('albums', $albums)->with('tags', $tags);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!is_numeric($id)) {
            return redirect()->back()->withErrors(['message', '화보 고유아이디 형식 오류!']);
        }

        $album = Album::find($id);
        $tags = $album->tags;

        return view('albums.editAlbum')->with('album', $album)->with('tags', $tags);
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
        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('edit_album_form', ['id' => $id])->withErrors($validator)->withInput();
        }

        $album = Album::find($id);
        $album->name = $request->get('name');
        $album->description = $request->get('description');
        $album->published_at = Carbon::now()->format('Y-m-d h:i:s');
        $album->retag($request->get('tags'));
        $album->save();

        return redirect()->route('show_album', ['id' => $id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $album = Album::find($id);

        $dataPath = '/data/albums';

        // 화보 커버이미지 삭제
        $destinationPath = $dataPath .'/'. substr($album->cover_image, 0, 4) .'/'. substr($album->cover_image, 4, 2) .'/'. substr($album->cover_image, 6, 2);
        $success = File::delete(public_path() .'/'. $destinationPath .'/'. $album->cover_image);

        //TODO: 앨범에 속한 이미지들(파일 & 레코드) 루프돌며 삭제

        if ($success) {
            $album->delete();
        } else {
            session()->flash('warning', '앨범 폴더 제거중 오류 발생. 다시 시도하여주십시오.');
        }

        return redirect()->route('index');
    }

    /**
     * Sort images in the album
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortImages(Request $request, $id)
    {
        $success = true;

        /**
         * Batch UPDATE
         * http://laravel.io/forum/03-01-2014-insert-or-update-multipile-rows?page=1
         *
         * UPDATE images
         * SET order = (case when id = '1111' then '0'
         * when id = '2222' then '1'
         * when id = '3333' then '2'
         * end)
         * WHERE id in ('1111', '2222', '3333') AND album_id = '@album_id';
         *
         * Refactoring (Afterwords...)
         * https://laracasts.com/discuss/channels/eloquent/laravel-eloquent-complicated-query-case-wherein
         */
        $table = (new \App\Image())->getTable();

        $sqlSet = "";
        $sqlWhere = "";

        $image_ids = explode(',', $request->get('image_ids'));

        $i = 0;
        foreach($image_ids as $image_id) {
            $sqlSet .= "WHEN '{$image_id}' THEN {$i} ";
            $sqlWhere .= "'{$image_id}',";
            $i++;
        }
        $sqlSet = "`order` = (CASE `id` {$sqlSet} END)";
        $sqlWhere = rtrim($sqlWhere, ',');  // 마지막 쉼표 제거
        $sqlWhere = "`album_id` = {$id} And `id` IN ($sqlWhere)";

        // prepare one query
        $sql = "UPDATE `{$table}` SET {$sqlSet} WHERE {$sqlWhere}";

        // Update
        $affected = DB::update(DB::raw($sql));

        $result = [
            'success' => $success,
            'affected' => $affected,
        ];

        return response()->json($result);
    }

    /**
     * Create banner of album
     *
     * @param $id
     * @return json
     */
    public function banner($id)
    {
        if (!isset($id)) {
            return redirect()->route('show_album', ['id' => $id])->withErrors('배너 아이디 누락')->withInput();
        }

        $success = false;

        $album = Album::with('Photos')->find($id);

        // 파일 수
        $totalCount = $album->Photos->count();
        // 파일 수가 6개가 안되면 오류 발생하고 종료
        $limitCount = 6;
        if ($totalCount < $limitCount) {
            return response()->json(['success' => $success, 'reason' => '6건 이상의 사진이 필요합니다. 사진추가하여 주십시오.']);
        }

        // 배경용 이미지 열기
        $bgImgPath = public_path() ."/images/album-banner-background.png";
        $img = Image::make($bgImgPath);

        // 배경위에 삽입할 좌표들을 미리 셋팅
        $axis = [
            [77, 9],
            [249, 9],
            [421, 9],
            [77, 140],
            [249, 140],
            [421, 140],
        ];

        $filePath = public_path() .'/data/albums';

        // 사용자 정의한 순서대로 re-sort
        $album->Photos = $album->Photos->sortBy('order');

        // 배경위에 파일들 삽입
        $photos = $album->Photos->take($limitCount);
        $i = 0;
        foreach($photos as $photo) {
            $fileFullPath = $filePath .'/'. substr($photo->image, 0, 4) .'/'. substr($photo->image, 4, 2) .'/'. substr($photo->image, 6, 2) .'/'. $photo->image;

            $pic = Image::make($fileFullPath);
            $pic->fit(160, 118);

            $x = $axis[$i][0];
            $y = $axis[$i][1];

            $img->insert($pic, 'top-left', $x, $y);
            $i++;
        }

        // 중앙 원형 이미지 삽입
        $buttonImgPath = public_path() ."/images/album-banner-center-circle-button.png";
        $buttonImg = Image::make($buttonImgPath);
        $img->insert($buttonImg, 'center');

        // 배너 경로
        $bannerPaths = explode("-", $album->created_at);
        $bannerPath = $filePath ."/". $bannerPaths[0] ."/". $bannerPaths[1];

        // 배너 파일명
        $bannerFileName = "banner{$id}.jpg";

        // 100 퀄리티로 저장
        $img->save($bannerPath ."/". $bannerFileName, 100);
        //return $img->response(); // (주석풀고) 브라우저 디버깅 = http://호스트/albums/createbanner/{id}

        // 결과 데이터 정리
        $rootUrl = $this->albumUrl;
        $bannerPath = str_replace(public_path() ."/", "", $bannerPath);
        $bannerPath = "{$rootUrl}/{$bannerPath}/{$bannerFileName}?ver=". time();    // 브라우저 캐시 갱신을 위해 append timestamp suffix
        $href = "{$rootUrl}/{$id}";

        // JSON 응답
        return response()->json([
            'success' => true,
            'banner' => $bannerPath,
            'href' => $href,
            'title' => $album->name .' 화보 - 모터그래프',
        ]);
    }

    /**
     * CSV format 앨범 번호들 받아서 앨범들 정보 리턴
     * @return \Illuminate\Http\JsonResponse
     * @internal param $ids
     */
    public function infos()
    {
        $ids = request()->get('ids');
        $ids = explode(",", $ids);
        $albums = Album::whereIn('id', $ids)->get();

        return response()->json(['albums' => $albums]);
    }

    /**
     * 화보 시작페이지 편집
     */
    public function home()
    {
        return view('albums.home');
    }

    /**
     * 화보 시작페이지 편집 처리
     */
    public function homeUpdate(Request $request)
    {
        $destPath = public_path() ."/data/albums";
        $destFile = $destPath ."/home.html";

        $success = false;

        if (File::exists($destFile)) {
            if (!File::isWritable($destFile)) {
                return response()->json(['success' => $success, 'reason' => '저장 파일에 쓰기 권한이 없습니다. 관리자에 문의하여 주십시오.']);
            }
        }

        $success = File::put($destFile, $request->get('htmlSnippet'));
        if (!$success) {
            return response()->json(['success' => $success, 'reason' => '저장중 장애 발생! 다시 시도하여 주십시오.']);
        }

        return response()->json(['success' => $success]);
    }

    public function homeAdd(Request $request)
    {
        $rules = [
            'name' => 'required',
            'link' => 'required',
            'cover_image' => 'required|image',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'reason' => $validator]);
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

        $file = $request->file('cover_image');
        $random_name = str_random(8);
        $extension = $file->getClientOriginalExtension();
        $filename = $random_name .'_cover.'. $extension;
        $uploadSuccess = $request->file('cover_image')->move($destinationPath, $filename);

        // 썸네일 생성
        if (!File::exists($destinationThumbPath .'/'. $filename)) {
            $srcPath = $destinationPath .'/'. $filename;
            $thumbPath = $destinationThumbPath .'/'. $filename;

            Image::make($srcPath)->fit($this->imgDimensions[0], $this->imgDimensions[1])->save($thumbPath);
        }

        $data = $request->all();
        $data['cover_image'] = '/'. $destinationThumbPath .'/'. $filename;

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * 관련기사 설정 폼
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relnews($id)
    {
        $album = [];

        if (isset($id)) {
            $album = Album::find($id);
        }

        $data = $album;

        return view('albums.relnews', ['data' => $data, 'album_id' => $id]);
    }

    /**
     * 관련기사 설정 처리
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function relnewsUpdate(Request $request)
    {
        $success = false;

        if (count($request->datas) > 0) {
            // Clear old
            $deletedRows = AlbumArticlesRelationship::where('album_id', $request->album_id)->delete();

            // Bulk insert
            AlbumArticlesRelationship::insert($request->datas);
            $success = true;
        }

        return response()->json(['success' => $success]);
    }
}
