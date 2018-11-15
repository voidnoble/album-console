<?php

namespace App\Http\Controllers\LiveVideos\Notice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redis;

/**
 * @brief 라이브 영상 하단의 공지 캡션 내용 관리
 * @package App\Http\Controllers\LiveVideos\Notice
 */
class LiveVideosNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $phrase = Redis::get('liveVideos:notice');
        } catch (\Exception $e) {
            //\Log::error(dd($e->getMessage()));
            abort(500, $e->getMessage());
        }

        return view('liveVideos.notice.index', ['phrase' => $phrase]);
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
        $phrase = $request->get('phrase');

        if (isset($phrase)) {
            Redis::set('liveVideos:notice', $phrase);
        }

        return redirect('/livevideos/notice');
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
        //
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
        //
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
