<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  string $dir
     * @return \Illuminate\Http\Response
     */
    public function index($dir)
    {
        $albumPath = Config::get('filesystems.paths.album');
        $path = "{$albumPath}/{$dir}";

        $files = File::all($path);

        return response()->json(['Controller' => 'FileController', 'function' => 'index']);
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string $dir
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($dir, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $dir
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($dir, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string $dir
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $dir, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $dir
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($dir, $id)
    {
        if (!isset($dir) || !isset($id)) {
            return response()->json(['success' => false]);
        }

        $index = request()->input("index");

        $success = false;

        $albumDataPath = Config::get('filesystems.paths.album');
        $dir = ($dir == "~" || $dir == "")? "" : str_replace(".", "/", $dir);
        $currentPath = "{$albumDataPath}/{$dir}";          // 현재 실제 경로
        $indexFile = "{$currentPath}/index.html";       // 정적 인덱스 파일
        $manifestFile = "{$currentPath}/manifest.json"; // 디렉토리의 정보를 담고 있는 파일
        $manifest = json_decode(File::get($manifestFile), true);    // 메타 정보 파일 로딩
        $manifest['dirs'] = array_values($manifest['dirs']);
        $manifest['files'] = array_values($manifest['files']);

        if ($manifest['carousel']) {
            $filePath = "{$currentPath}/slides/{$id}";
        } else {
            $filePath = "{$currentPath}/{$id}";
        }

        if (File::exists($filePath)) {
            // Delete file
            $success = File::delete($filePath);
            // if Carousel dir then also remove a thumb file
            if ($manifest['carousel']) {
                File::delete(str_replace("/slides", "/thumbs", $filePath));
            }
            // file successfully deleted
            if ($success != false) {
                // Remove file row from manifest array with basename
                if (isset($index)) {
                    unset($manifest['files'][$index]);
                    // Save the removed manifest result
                    File::put($manifestFile, json_encode($manifest, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        return response()->json(['success' => $success, 'index' => $index]);
    }
}
