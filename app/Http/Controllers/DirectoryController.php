<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

/**
 * @brief 디렉토리 컨트롤러
 * @package App\Http\Controllers
 * @description
 *  $manifest['files'] 의 두번째 항목 제거 가정시 json_encode(...) 로 저장하면
 *  순차배열이 "0": {}, "1": {}, "3" 과 같이 해시 배열로 바뀌어 저장된다.
 *  해당 문제를 항상 개선하여 저장하고싶다면 모든 json_encode() 이후 아래 3줄의 코드를 통해 json 파일을 재저장.
 *  $manifestJsonString = File::get($manifestFile);
 *  $manifestJsonString = preg_replace("/\"\d+\":/i", "", $manifestJsonString);
 *  File::put($manifestFile, $manifestJsonString);
 *
 *  현재는 위 문제를
 *  json_decode(...) 로 불러왔을때 배열을 array_values()를 통해 순차 배열로 변환하여 처리중.
 */
class DirectoryController extends Controller
{
    private $skipDirNames = "",
        $protocol = "http",
        $rootUrl;

    public function __construct()
    {
        $this->skipDirNames = "res|slides|thumbs|app|data|bootstrap|bower_components|config|database|node_modules|public|resources|storage|tests|vendor|venv|env";
        $this->protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')? "https" : "http";
        $this->rootUrl = $this->protocol ."://". Config::get('app.domain.album');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->show("");
    }

    /**
     * method = 'GET' : Show the form for creating a new resource.
     * method = 'POST' : Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $method = request()->method();

        if ($method == 'POST') {
            $dir = $pwd = request()->input('pwd');
            $data['realname'] = request()->input('realname');
            $data['title'] = request()->input('title');

            if (!isset($pwd) || !isset($data['realname']) || !isset($data['title'])) {
                return response()->json(['success' => false, 'reason' => 'param require']);
            }

            /* 유니크하고 한글에 안전한 명칭 사용하려면 주석 해제
            $data['realname'] = time() ."-". md5($data['title']);*/

            $rootUrl = $this->rootUrl;
            $albumDataPath = Config::get('filesystems.paths.album');
            $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $pwd);             // 현재 상대 경로
            $currentPath = ($dir != "")? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
            $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
            $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일
            $data['dirname'] = "{$dir}/". $data['realname'];
            $data['url'] = $rootUrl ."/". $data['dirname'];
            $data['filecount'] = 0;
            $data['size'] = 0;

            $destPath = $currentPath ."/". $data['realname'];
            $success = File::makeDirectory($destPath);
            if ($success) {
                $manifest = json_decode(File::get($manifestFile), true);
                $manifest['dirs'] = array_values($manifest['dirs']);
                $manifest['files'] = array_values($manifest['files']);
                $manifest['dirs'][] = $data;
                File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));

                // 하위 경로의 메타정보 파일에 제목 셋팅
                $manifestFile = "{$destPath}/manifest.json";
                if (File::exists($manifestFile)) {
                    $manifest = json_decode(File::get($manifestFile), true);
                    $manifest['dirs'] = array_values($manifest['dirs']);
                    $manifest['files'] = array_values($manifest['files']);
                } else {
                    $manifest = [
                        "carousel" => false,
                        "dirs" => [],
                        "files" => [],
                    ];
                }
                $manifest['title'] = $data['title'];
                File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
            }

            return response()->json(['success' => $success, 'data' => $data]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * http://laravel.com/docs/5.1/requests#files
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "files" => "required",
        ]);

        $success = false;

        $rootUrl = $this->rootUrl;
        $dir = $request->input("dir");
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);
        $albumDataPath = Config::get('filesystems.paths.album');
        $currentPath = "{$albumDataPath}/{$dir}";
        $manifestFile = "{$currentPath}/manifest.json";

        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            $manifest['dirs'] = array_values($manifest['dirs']);
            $manifest['files'] = array_values($manifest['files']);
        } else {
            $manifest = [
                'carousel' => false,
            ];
        }

        $files = $request->file("files");
        $file_count = count($files);
        $uploaded_count = 0;
        $metas = $request->input("metas");

        $slidePath = "{$currentPath}/slides";
        $thumbPath = "{$currentPath}/thumbs";
        // 슬라이드 디렉토리 생성
        if (!File::exists($slidePath)) {
            File::makeDirectory($slidePath);
        }
        // 썸네일 디렉토리 생성
        if (!File::exists($thumbPath)) {
            File::makeDirectory($thumbPath);
        }

        if ($manifest['carousel']) {
            $destinationPath = $slidePath;
        } else {
            $destinationPath = $currentPath;
        }

        for($i = 0; $i < $file_count; $i++) :
            $file = $files[$i];

            // 제목이 지정되지 않았으면 원본 파일명으로 대체
            if ($metas[$i]['title'] == "") {
                $metas[$i]['title'] = str_replace(".". $file->getClientOriginalExtension(), "", $file->getClientOriginalName());
            }

            //if ($request->hasFile("file") && $request->file("file")->isValid()) {
                $fileName = time() ."-". md5($file->getClientOriginalName()) .".". $file->getClientOriginalExtension();

                if ($file->move($destinationPath, $fileName)) {
                    if (File::exists("{$destinationPath}/{$fileName}")) {
                        $uploaded_count++;

                        $fileInfo = pathinfo("{$destinationPath}/{$fileName}");
                        $fileInfo['dirname'] = str_replace($albumDataPath, "", $fileInfo['dirname']);
                        $fileInfo['url'] = $rootUrl . $fileInfo['dirname'] . "/" . $fileInfo['basename'];
                        $fileInfo['thumburl'] = str_replace("/slides", "/thumbs", $fileInfo['url']);
                        $fileInfo['size'] = File::size($file);

                        // 썸네일 없으면 생성
                        if (!File::exists($thumbPath . "/" . $fileInfo['basename'])) {
                            $imgFullPath = $albumDataPath . $fileInfo['dirname'] . "/" . $fileInfo['basename'];
                            $thumbFullPath = $thumbPath . "/" . $fileInfo['basename'];
                            Image::make($imgFullPath)->fit(360, 300)->save($thumbFullPath);

                            $fileInfo['thumburl'] = "{$rootUrl}/{$dir}/thumbs/" . $fileInfo['basename'];
                        }

                        if ($manifest['carousel']) {
                            $fileInfo['dirname'] = str_replace("/slides", "", $fileInfo['dirname']);
                        }

                        $metas[$i] = array_merge($metas[$i], $fileInfo);
                    }
                }
            //}
        endfor;

        if ($uploaded_count == $file_count) {
            // Write file collection to the meta file
            $manifest['files'] = array_merge($manifest['files'], $metas);
            $success = File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
        }

        $result = [
            'success' => $success,
            'path' => $dir,
            'files' => $metas
        ];

        return response()->json($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function show($dir)
    {
        $success = false;

        $rootUrl = $this->rootUrl;
        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);             // 현재 상대 경로
        $currentPath = ($dir != "")? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일
        $carousel = false;                              // 캐러셀인가?
        $rescan = "no";
        $manifest = [];

        // 현재 경로에 파일이나 디렉토리가 있으면
        if (File::exists($currentPath)) {
            // 메타 정보파일이 없으면
            if (!File::exists($manifestFile)) {
                $rescan = "yes";    // 재스캔 시행
            } else { // 메타 정보파일이 있지만 강제 재스캔 쿼리스트링이 있을 경우
                if (request()->get("rescan") == "yes") $rescan = "yes";
                // 기존 메타 정보 로딩
                $manifest = json_decode(File::get($manifestFile), true);
                $manifest['title'] = (isset($manifest['dirs']))? $manifest['dirs'] : "";
                $manifest['dirs'] = array_values($manifest['dirs']);
                $manifest['files'] = array_values($manifest['files']);
            }

            // 재스캔 시행이면
            if ($rescan == "yes") {
                $dirs = File::directories($currentPath);
                $csvDir = implode(",", $dirs);
                $thumbPath = "{$currentPath}/thumbs";

                // if 최종 디렉토리 then 슬라이드 뷰 디렉토리를 현재 디렉토리로 할당
                if ($carousel == true || str_contains($csvDir, "slides") || str_contains($csvDir, "thumbs")) {
                    $currentPath .= "/slides";
                    $dirs = [];
                    $carousel = true;
                } else {
                    $tmpDirs = $dirs;
                    $dirs = [];
                    for($i = 0; $i < count($tmpDirs); $i++) {
                        $dir = $tmpDirs[$i];

                        // 쓸데없는 폴더 Skip
                        if (preg_match("/(". $this->skipDirNames .")$/i", $dir)) continue;

                        // 폴더명 쌓기
                        $dirs[$i]["dirname"] = str_replace("{$albumDataPath}/", "", $dir);
                        $dirs[$i]["realname"] = str_replace("{$currentPath}/", "", $dir);
                        $dirs[$i]["title"] = isset($manifest['dirs'][$i]['title'])? $manifest['dirs'][$i]['title'] : $dirs[$i]["realname"];
                        $dirs[$i]["url"] = "{$rootUrl}/{$dirs[$i]['dirname']}";
                        $dirs[$i]["filecount"] = count(File::files($dir));
                        $dirs[$i]["size"] = File::size($dir);
                    }
                    $dirs = array_values($dirs);
                }
                unset($csvDir);

                // 현재 경로의 파일들 처리
                $files = File::files($currentPath);
                $tmpFiles = $files;
                $files = [];
                $i = 0;
                foreach($tmpFiles as $file) {
                    $fileInfo = pathinfo($file);
                    $fileInfo['title'] = "";
                    $fileInfo['description'] = "";
                    $fileInfo['thumburl'] = "";

                    // foreach 현재 작업 파일 = 폴더 이미지 파일 then Next ...
                    if (preg_match("/(folderimage|folderthumb|banner)/i", $fileInfo['filename'])) continue;

                    // 파일명이 한글이나 공백, 탭 존재하면 유니크한 파일명으로
                    if (preg_match("/([\xA1-\xFE][\xA1-\xFE]|\s|\t)/", $fileInfo['filename'])) {
                        $srcBasename = preg_replace("/.+\/(.+)$/", "$1", $file);
                        $destBasename = time() ."-". md5(str_replace(".". $fileInfo['extension'], "", $srcBasename)) .".". strtolower($fileInfo['extension']);
                        $src = $currentPath ."/". $srcBasename;
                        $dest = $currentPath ."/". $destBasename;
                        File::move($src, $dest);

                        $fileInfo['title'] = str_replace(".". $fileInfo['extension'], "", $srcBasename);
                        $fileInfo['basename'] = $destBasename;
                        $fileInfo['extension'] = strtolower($fileInfo['extension']);
                        $fileInfo['filename'] = str_replace(".". $fileInfo['extension'], "", $destBasename);
                    }

                    $fileInfo['dirname'] = str_replace($albumDataPath, "", $fileInfo['dirname']);
                    $fileInfo['url'] = $rootUrl . $fileInfo['dirname'] ."/". $fileInfo['basename'];
                    $fileInfo['size'] = File::size($currentPath ."/". $fileInfo['basename']);

                    // 이미 저장된 메타 정보가 있을 경우
                    if (isset($manifest['files'][$i])) {
                        $manifestRow = $manifest['files'][$i];
                        $fileInfo['title'] = (isset($manifestRow['title']))? $manifestRow['title'] : $fileInfo['filename'];
                        $fileInfo['description'] = (isset($manifestRow['description']))? $manifestRow['description'] : "";
                        $fileInfo['thumburl'] = (isset($manifestRow['thumburl']))? $manifestRow['thumburl'] : "";
                    // 저장된 메타 정보가 없을 경우
                    } else {
                        if ($fileInfo['title'] == "") $fileInfo['title'] = $fileInfo['filename'];
                    }

                    // 슬라이드 디렉토리일때
                    if ($carousel) {
                        // 썸네일 없으면 생성
                        if (!File::exists($thumbPath . "/" . $fileInfo['basename'])) {
                            $imgFullPath = $albumDataPath . $fileInfo['dirname'] . "/" . $fileInfo['basename'];
                            $thumbFullPath = $thumbPath . "/" . $fileInfo['basename'];
                            Image::make($imgFullPath)->fit(360, 300)->save($thumbFullPath);
                        }

                        // 썸네일 파일 URL 할당
                        $fileInfo['thumburl'] = "{$rootUrl}/{$dir}/thumbs/" . $fileInfo['basename'];

                        // 앨범 데이터 (루트 디렉토리로부터의) 상대경로
                        $fileInfo['dirname'] = "/{$dir}";
                    } else {
                        if ($fileInfo['thumburl'] == "") {
                            $fileInfo['thumburl'] = $fileInfo['url'];
                        }
                    }

                    // 최종적으로 foreach 현재 작업 파일이 이미지이면 파일배열에 메타 정보 push
                    if (isset($fileInfo['extension'])) {
                        if (preg_match("/(jpg|png|gif)/i", $fileInfo['extension'])) {
                            $files[] = $fileInfo;
                        }
                    }

                    ++$i;
                }

                // 결과 push
                $manifest = [
                    "title" => "",
                    "carousel" => $carousel,
                    "dirs" => $dirs,
                    "files" => $files,
                ];

                // 메타 파일에 결과 저장
                $success = File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));

                $result = [
                    'success' => $success,
                ];
            } else {    // 재스캔 필요 없으면 메타 파일 내용 조회
                $result = json_decode(File::get($manifestFile), true);
                $manifest['dirs'] = array_values($manifest['dirs']);
                $manifest['files'] = array_values($manifest['files']);
            }

            // 현재 디렉토리에 index.html 파일 없으면 생성
            if (!File::exists($indexFile)) {
                // View data
                $viewData = [
                    'dir' => $dir,
                    'title' => $manifest['title'],
                    'files' => $manifest['files'],
                    'description' => ' ',
                ];
                $albumIndexTemplate = view("albumIndexTemplate", $viewData)->render();
                File::put($indexFile, $albumIndexTemplate);
            }
        } else {
            $result = [
                'success' => $success,
                'reason' => 'The directory not exists.'
            ];
        }

        return response()->json($result);
    }

    /**
     * Rescan and regenerate manifest.json the specified resource.
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function rescan($dir)
    {
        $success = false;

        $rootUrl = $this->rootUrl;
        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);             // 현재 상대 경로
        $currentPath = ($dir != "")? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일

        // 기존 데이터 로딩
        $manifest = json_decode(File::get($manifestFile), true);
        $manifest['dirs'] = array_values($manifest['dirs']);
        $manifest['files'] = array_values($manifest['files']);
        $title = (isset($manifest['title']))? $manifest['title'] : " ";
        $description = (isset($manifest['description']))? $manifest['description'] : " ";
        $carousel = (isset($manifest['carousel']))? $manifest['carousel'] : false;

        // 재스캔이므로 메타정보 초기화
        $manifest['title'] = $title; // 현 폴더 제목 그대로 사용

        // 현재 경로에 파일이나 디렉토리가 있으면
        if (File::exists($currentPath)) {
            $dirs = File::directories($currentPath);
            $csvDir = implode(",", $dirs);
            $slidePath = "{$currentPath}/slides";
            $thumbPath = "{$currentPath}/thumbs";

            // if 최종 디렉토리 then 슬라이드 뷰 디렉토리를 현재 디렉토리로 할당
            if ($carousel) {
                $dirs = [];
                $currentPath = $slidePath;
            } else {
                $tmpDirs = $dirs;
                $dirs = [];
                for($i = 0; $i < count($tmpDirs); $i++) {
                    $dir = $tmpDirs[$i];

                    // 쓸데없는 폴더 Skip
                    if (preg_match("/(". $this->skipDirNames .")$/i", $dir)) continue;

                    // 폴더명 쌓기
                    $dirs[$i]["dirname"] = str_replace("{$albumDataPath}/", "", $dir);
                    $dirs[$i]["realname"] = str_replace("{$currentPath}/", "", $dir);
                    $dirs[$i]["title"] = isset($manifest['dirs'][$i]['title'])? $manifest['dirs'][$i]['title'] : $dirs[$i]["realname"];
                    $dirs[$i]["url"] = "{$rootUrl}/{$dirs[$i]['dirname']}";
                    $dirs[$i]["filecount"] = count(File::files($dir));
                    $dirs[$i]["size"] = File::size($dir);
                }
                $dirs = array_values($dirs);
            }
            unset($csvDir);

            // 현재 경로의 파일들 처리
            $files = File::files($currentPath);
            $tmpFiles = $files;
            $files = [];
            $i = 0;
            foreach($tmpFiles as $file) {
                $fileInfo = pathinfo($file);
                $fileInfo['title'] = "";
                $fileInfo['description'] = "";
                $fileInfo['thumburl'] = "";

                // foreach 현재 작업 파일 = 폴더 이미지 파일 then Next ...
                if (preg_match("/(folderimage|folderthumb|banner)/i", $fileInfo['filename'])) continue;
                if (!preg_match("/(jpg|png|gif)/i", $fileInfo['extension'])) continue;

                // 파일명이 한글이나 공백, 탭 존재하면 유니크한 파일명으로
                if (preg_match("/([\xA1-\xFE][\xA1-\xFE]|\s|\t)/", $fileInfo['filename'])) {
                    $srcBasename = preg_replace("/.+\/(.+)$/", "$1", $file);
                    $destBasename = time() ."-". md5(str_replace(".". $fileInfo['extension'], "", $srcBasename)) .".". strtolower($fileInfo['extension']);
                    $src = $currentPath ."/". $srcBasename;
                    $dest = $currentPath ."/". $destBasename;
                    File::move($src, $dest);

                    $fileInfo["title"] = isset($manifest['files'][$i]['title'])? $manifest['files'][$i]['title'] : str_replace(".". $fileInfo['extension'], "", $srcBasename);
                    $fileInfo['basename'] = $destBasename;
                    $fileInfo['extension'] = strtolower($fileInfo['extension']);
                    $fileInfo['filename'] = str_replace(".". $fileInfo['extension'], "", $destBasename);
                }

                $fileInfo['dirname'] = str_replace($albumDataPath, "", $fileInfo['dirname']);
                $fileInfo['url'] = $rootUrl . $fileInfo['dirname'] ."/". $fileInfo['basename'];
                $fileInfo['size'] = File::size($currentPath ."/". $fileInfo['basename']);

                // 기존 메타정보로부터 overwrite
                for($j = 0; $j < count($manifest['files']); $j++) {
                    $manifestInfo = $manifest['files'][$i];
                    if ($manifestInfo['basename'] == $fileInfo['basename']) {
                        $fileInfo['title'] = (isset($manifestInfo['title']))? $manifestInfo['title'] : $fileInfo['filename'];
                        $fileInfo['description'] = (isset($manifestInfo['description']))? $manifestInfo['description'] : "";
                        $fileInfo['thumburl'] = (isset($manifestInfo['thumburl']))? $manifestInfo['thumburl'] : "";

                        break;
                    }
                }

                // 여태까지 제목이 없으면 파일명으로 대체
                if ($fileInfo['title'] == "") $fileInfo['title'] = $fileInfo['filename'];

                // 슬라이드 디렉토리일때
                if ($carousel) {
                    // 썸네일 없으면 생성
                    if (!File::exists($thumbPath . "/" . $fileInfo['basename'])) {
                        $imgFullPath = $albumDataPath . $fileInfo['dirname'] . "/" . $fileInfo['basename'];
                        $thumbFullPath = $thumbPath . "/" . $fileInfo['basename'];
                        Image::make($imgFullPath)->fit(360, 300)->save($thumbFullPath);
                    }

                    // 썸네일 파일 URL 할당
                    $fileInfo['thumburl'] = "{$rootUrl}/{$dir}/thumbs/" . $fileInfo['basename'];

                    // 앨범 데이터 (루트 디렉토리로부터의) 상대경로
                    $fileInfo['dirname'] = "/{$dir}";
                } else {
                    if ($fileInfo['thumburl'] == "") {
                        $fileInfo['thumburl'] = $fileInfo['url'];
                    }
                }

                // 최종적으로 foreach 현재 작업 파일이 이미지이면 파일배열에 메타 정보 push
                if (isset($fileInfo['extension'])) {
                    if (preg_match("/(jpg|png|gif)/i", $fileInfo['extension'])) {
                        $files[] = $fileInfo;
                    }
                }

                ++$i;
            }

            // 결과 push
            $manifest = [
                "title" => $title,
                "description" => $description,
                "carousel" => $carousel,
                "dirs" => $dirs,
                "files" => $files,
            ];

            // 메타 파일에 결과 저장
            $success = File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));

            $result = [
                'success' => $success,
            ];

            // 현재 디렉토리에 index.html 파일 재생성
            if (File::exists($indexFile)) {
                File::delete($indexFile);
            }
            // View data
            $viewData = [
                'dir' => $dir,
                'title' => $title,
                'description' => $description,
                "files" => $files,
            ];
            $albumIndexTemplate = view("albumIndexTemplate", $viewData)->render();
            File::put($indexFile, $albumIndexTemplate);

            // 슬라이드 디렉토리이면 현재 디렉토리 폴더이미지 생성
            if ($carousel) {
                $currentPath = ($dir != "")? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로

                $folderImgPath = "{$currentPath}/folderimage.jpg";
                $folderThumbPath = "{$currentPath}/folderthumb.jpg";

                //if (!File::exists($folderImgPath)) {  // Rescan 이므로 무조건 생성
                    $srcPath = "{$slidePath}/" . $files[0]['basename'];

                    Image::make($srcPath)->fit(900, 240)->save($folderImgPath);
                    Image::make($srcPath)->fit(360, 300)->save($folderThumbPath);
                //}
            }
        } else {
            $result = [
                'success' => $success,
                'reason' => 'The directory not exists.'
            ];
        }

        return response()->json($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function edit($dir)
    {
        return response()->json(['success' => true, 'reason' => $dir]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $dir)
    {
        $this->validate(request(), [
            "name" => "required",
            "title" => "required",
        ]);

        $success = false;

        $index = $request->input("index");
        $realname = $request->input("name");
        $title = $request->input("title");

        if (!isset($index)) {
            return response()->json(['success' => $success, 'reason' => 'index required']);
        }
        if (!isset($realname)) {
            return response()->json(['success' => $success, 'reason' => 'folder real name required']);
        }
        if (!isset($title)) {
            return response()->json(['success' => $success, 'reason' => 'folder name required']);
        }

        $rootUrl = $this->rootUrl;
        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "") ? "" : str_replace(".", "/", $dir);
        $targetDir = basename($dir);
        $dir = preg_replace("/\/?{$targetDir}$/", "", $dir);
        $currentPath = ($dir != "") ? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일

        // 기존 메타 데이터 로딩
        $manifest = json_decode(File::get($manifestFile), true);
        $manifest['dirs'] = array_values($manifest['dirs']);
        $manifest['files'] = array_values($manifest['files']);

        $oldname = $manifest['dirs'][$index]['realname'];
        $old = "{$currentPath}/{$oldname}";
        $new = "{$currentPath}/{$realname}";

        // 원본 디렉토리 존재 검증
        if (!File::exists($old)) {
            return response()->json(['success' => $success, 'reason' => 'Source folder not exist']);
        }

        // 기존 디렉토리명을 변경시 대상 디렉토리 중복 검사
        if ($oldname != $realname && File::exists($new)) {
            return response()->json(['success' => $success, 'reason' => 'Target folder already exist']);
        }

        // 디렉토리 수정
        File::move($old, $new);

        // 기존 메타 데이터 변경
        $manifest['dirs'][$index]['realname'] = $realname;
        $manifest['dirs'][$index]['title'] = $title;
        // 메타 파일에 저장
        $success = File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
        unset($manifest);

        // 하위 경로의 메타정보 파일에 제목 셋팅 & 파일 경로들 업데이트
        $destPath = $new;
        $manifestFile = "{$destPath}/manifest.json";
        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            $manifest['dirs'] = array_values($manifest['dirs']);
            $manifest['files'] = array_values($manifest['files']);
        } else {
            $manifest['carousel'] = $carousel = false;
            $manifest['dirs'] = [];
            $manifest['files'] = [];

            // 하위 경로 스캔하여 메타정보 셋팅
            if (File::exists($destPath)) {
                $dirs = File::directories($destPath);
                $slidePath = "{$destPath}/slides";
                $thumbPath = "{$destPath}/thumbs";

                // carousel directory?
                if (File::exists($slidePath)) {
                    $carousel = true;
                }

                // if 최종 디렉토리 then 슬라이드 뷰 디렉토리를 현재 디렉토리로 할당
                if ($carousel) {
                    $dirs = [];
                    $destPath = $slidePath;
                } else {
                    $tmpDirs = $dirs;
                    $dirs = [];
                    for($i = 0; $i < count($tmpDirs); $i++) {
                        $dir = $tmpDirs[$i];

                        // 쓸데없는 폴더 Skip
                        if (preg_match("/(". $this->skipDirNames .")$/i", $dir)) continue;

                        // 폴더명 쌓기
                        $dirs[$i]["dirname"] = str_replace("{$albumDataPath}/", "", $dir);
                        $dirs[$i]["realname"] = str_replace("{$destPath}/", "", $dir);
                        $dirs[$i]["title"] = isset($manifest['dirs'][$i]['title'])? $manifest['dirs'][$i]['title'] : $dirs[$i]["realname"];
                        $dirs[$i]["url"] = "{$rootUrl}/{$dirs[$i]['dirname']}";
                        $dirs[$i]["filecount"] = count(File::files($dir));
                        $dirs[$i]["size"] = File::size($dir);
                    }
                    $dirs = array_values($dirs);
                }

                // 대상 경로의 파일들 처리
                $files = File::files($destPath);
                $tmpFiles = $files;
                $files = [];
                $i = 0;
                foreach($tmpFiles as $file) {
                    $fileInfo = pathinfo($file);
                    $fileInfo['title'] = "";
                    $fileInfo['description'] = "";
                    $fileInfo['thumburl'] = "";

                    // foreach 현재 작업 파일 = 폴더 이미지 파일 then Next ...
                    if (preg_match("/(folderimage|folderthumb|banner)/i", $fileInfo['filename'])) continue;
                    if (!preg_match("/(jpg|png|gif)/i", $fileInfo['extension'])) continue;

                    // 파일명이 한글이나 공백, 탭 존재하면 유니크한 파일명으로
                    if (preg_match("/([\xA1-\xFE][\xA1-\xFE]|\s|\t)/", $fileInfo['filename'])) {
                        $srcBasename = preg_replace("/.+\/(.+)$/", "$1", $file);
                        $destBasename = time() ."-". md5(str_replace(".". $fileInfo['extension'], "", $srcBasename)) .".". strtolower($fileInfo['extension']);
                        $src = $destPath ."/". $srcBasename;
                        $dest = $destPath ."/". $destBasename;
                        File::move($src, $dest);

                        $fileInfo["title"] = isset($manifest['files'][$i]['title'])? $manifest['files'][$i]['title'] : str_replace(".". $fileInfo['extension'], "", $srcBasename);
                        $fileInfo['basename'] = $destBasename;
                        $fileInfo['extension'] = strtolower($fileInfo['extension']);
                        $fileInfo['filename'] = str_replace(".". $fileInfo['extension'], "", $destBasename);
                    }

                    $fileInfo['dirname'] = str_replace($albumDataPath, "", $fileInfo['dirname']);
                    $fileInfo['url'] = $rootUrl . $fileInfo['dirname'] ."/". $fileInfo['basename'];
                    $fileInfo['size'] = File::size($destPath ."/". $fileInfo['basename']);

                    // 제목은 파일명으로 대체
                    if ($fileInfo['title'] == "") $fileInfo['title'] = $fileInfo['filename'];

                    // 슬라이드 디렉토리일때
                    if ($carousel) {
                        // 썸네일 없으면 생성
                        if (!File::exists($thumbPath . "/" . $fileInfo['basename'])) {
                            $imgFullPath = $albumDataPath . $fileInfo['dirname'] . "/" . $fileInfo['basename'];
                            $thumbFullPath = $thumbPath . "/" . $fileInfo['basename'];
                            Image::make($imgFullPath)->fit(360, 300)->save($thumbFullPath);
                        }

                        // 썸네일 파일 URL 할당
                        $fileInfo['thumburl'] = "{$rootUrl}/{$dir}/thumbs/" . $fileInfo['basename'];

                        // 앨범 데이터 (루트 디렉토리로부터의) 상대경로
                        $fileInfo['dirname'] = "/{$dir}";
                    } else {
                        if ($fileInfo['thumburl'] == "") {
                            $fileInfo['thumburl'] = $fileInfo['url'];
                        }
                    }

                    // 최종적으로 foreach 현재 작업 파일이 이미지이면 파일배열에 메타 정보 push
                    if (isset($fileInfo['extension'])) {
                        if (preg_match("/(jpg|png|gif)/i", $fileInfo['extension'])) {
                            $files[] = $fileInfo;
                        }
                    }

                    ++$i;
                }

                // 결과 push
                $manifest = [
                    "carousel" => $carousel,
                    "dirs" => $dirs,
                    "files" => $files,
                ];
            }
        }

        $manifest['title'] = $title;
        if (count($manifest['files']) > 0) {
            for($i = 0; $i < count($manifest['files']); $i++) {
                $manifest['files'][$i]['dirname'] = str_replace($oldname, $realname, $manifest['files'][$i]['dirname']);
                $manifest['files'][$i]['url'] = str_replace($oldname, $realname, $manifest['files'][$i]['url']);
                $manifest['files'][$i]['thumburl'] = str_replace($oldname, $realname, $manifest['files'][$i]['thumburl']);
            }
        }

        File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
        unset($manifest);

        return response()->json([
            'success' => $success,
            'index' => $index
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function destroy($dir)
    {
        if (!isset($dir)) {
            return response()->json(['success' => false]);
        }

        $success = false;

        $index = request()->json("index");

        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);
        $targetFullPath = "{$albumDataPath}/{$dir}";    // 대상 전체 경로
        $currentPath = preg_replace("/\/?". basename($dir) ."$/", "", $targetFullPath);  // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일

        if (File::exists($targetFullPath)) {
            $success = File::deleteDirectory($targetFullPath);
            if ($success) {
                // JSON to array
                $manifest = json_decode(File::get($manifestFile), true);
                $manifest['dirs'] = array_values($manifest['dirs']);
                $manifest['files'] = array_values($manifest['files']);

                // Remove row from manifest
                if (isset($index)) {
                    unset($manifest['dirs'][$index]);
                    // Save
                    File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        return response()->json(['success' => $success, 'index' => $index]);
    }

    /**
     * Setup carousel to the specified dir.
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function setup($dir)
    {
        if (!isset($dir)) {
            return response()->json(['success' => false]);
        }

        $this->validate(request(), [
            'title' => 'required|max:255',
            'description' => 'max:255',
        ]);

        $title = request()->input("title");
        $description = request()->input("description");

        $success = false;

        $rootUrl = $this->rootUrl;
        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);             // 현재 상대 경로
        $currentPath = ($dir != "")? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일
        $manifest = json_decode(File::get($manifestFile), true);
        $manifest['dirs'] = array_values($manifest['dirs']);
        $manifest['files'] = array_values($manifest['files']);
        $manifest['carousel'] = true;

        $slidePath = "{$currentPath}/slides";
        $thumbPath = "{$currentPath}/thumbs";
        // 슬라이드 디렉토리 생성
        if (!File::exists($slidePath)) {
            File::makeDirectory($slidePath);
        }
        // 썸네일 디렉토리 생성
        if (!File::exists($thumbPath)) {
            File::makeDirectory($thumbPath);
        }

        $files = $manifest['files'];    // 원본 내용은 작업용 변수에 할당하고
        $manifest['files'] = [];        // 초기화

        foreach($files as $fileInfo) {
            $srcPath = $currentPath ."/". $fileInfo['basename'];

            // 슬라이드 경로로 파일 이동
            if (File::exists($srcPath)) {
                $destPath = $slidePath ."/". $fileInfo['basename'];
                File::move($srcPath, $destPath);
            } elseif (File::exists($slidePath ."/". $fileInfo['basename'])) {
                $destPath =  $slidePath ."/". $fileInfo['basename'];
            }

            // 썸네일 생성
            if (isset($destPath)) {
                if (File::exists($thumbPath . "/" . $fileInfo['basename'])) File::delete($thumbPath . "/" . $fileInfo['basename']);
                Image::make($destPath)->fit(360, 300)->save($thumbPath . "/" . $fileInfo['basename']);
            }

            if (!strpos($fileInfo['url'], "/slides")) $fileInfo['url'] = str_replace($dir, "{$dir}/slides", $fileInfo['url']);
            if (!strpos($fileInfo['thumburl'], "/thumbs")) $fileInfo['thumburl'] = str_replace($dir, "{$dir}/thumbs", $fileInfo['thumburl']);

            $manifest['files'][] = $fileInfo;
        }

        $manifest['title'] = $title;
        $manifest['description'] = $description;

        // 메타 정보 저장
        $success = File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));

        // 현재 디렉토리에 index.html 파일 재생성
        if (File::exists($indexFile)) {
            File::delete($indexFile);
        }
        // View data
        $viewData = [
            'dir' => $dir,
            'title' => $title,
            'description' => $description,
            'files' => $manifest['files'],
        ];
        $albumIndexTemplate = view("albumIndexTemplate", $viewData)->render();
        File::put($indexFile, $albumIndexTemplate);

        // 현재 디렉토리 폴더이미지 생성
        $folderImgPath = "{$currentPath}/folderimage.jpg";
        $folderThumbPath = "{$currentPath}/folderthumb.jpg";
        if (!File::exists($folderImgPath)) {
            $srcPath = "{$slidePath}/". $files[0]['basename'];

            Image::make($srcPath)->fit(900, 240)->save($folderImgPath);
            Image::make($srcPath)->fit(360, 300)->save($folderThumbPath);
        }

        return response()->json(['success' => $success, 'datas' => $manifest]);
    }

    /**
     * Create banner file with specified dir
     *
     * @param  string  $dir
     * @return \Illuminate\Http\Response
     */
    public function banner($dir)
    {
        if (!isset($dir)) {
            return response()->json(['success' => false]);
        }

        $success = false;

        $rootUrl = $this->rootUrl;
        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "") ? "" : str_replace(".", "/", $dir);             // 현재 상대 경로
        $currentPath = ($dir != "") ? "{$albumDataPath}/{$dir}" : $albumDataPath;       // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일
        $manifest = json_decode(File::get($manifestFile), true);    // 메타 정보 파일 로딩
        $manifest['dirs'] = array_values($manifest['dirs']);
        $manifest['files'] = array_values($manifest['files']);
        $title = (isset($manifest['title']))? $manifest['title'] : preg_replace("/^.+\/(.+)$/", "$1", $dir);

        // 파일 수
        $totalCount = count($manifest['files']);
        // 파일 수가 6개가 안되면 오류 발생하고 종료
        if ($totalCount < 6) {
            return response()->json(['success' => false, 'reason' => '6건 이상의 이미지가 필요합니다']);
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

        // 배경위에 파일들 삽입
        for($i = 0; $i < 6; $i++) {
            $file = $manifest['files'][$i];

            // foreach 현재 작업 파일 = 폴더 이미지 파일 then Next ...
            if (preg_match("/(folderimage|folderthumb|banner)/i", $file['filename'])) continue;

            $picPath = $albumDataPath . $file['dirname'] ."/slides/". $file['basename'];
            if (!File::exists($picPath)) continue;

            $pic = Image::make($picPath);
            $pic->fit(160, 118);

            $x = $axis[$i][0];
            $y = $axis[$i][1];

            $img->insert($pic, 'top-left', $x, $y);
        }

        // 중앙 원형 이미지 삽입
        $buttonImgPath = public_path() ."/images/album-banner-center-circle-button.png";
        $buttonImg = Image::make($buttonImgPath);
        $img->insert($buttonImg, 'center');

        // 100 퀄리티로 저장
        $img->save($currentPath ."/banner.jpg", 100);

        //return $img->response(); // (주석풀고) 브라우저 디버깅 = http://호스트/api/album/v1/dirs/2015.temp/banner

        $href = "{$rootUrl}/{$dir}/";
        $bannerPath = "{$href}/banner.jpg";

        return response()->json([
            'success' => true,
            'banner' => $bannerPath,
            'href' => $href,
            'title' => $title
        ]);
    }
}
