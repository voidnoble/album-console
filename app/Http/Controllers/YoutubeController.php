<?php

namespace App\Http\Controllers;

use Alaouy\Youtube\Facades\Youtube;
use Illuminate\Http\Request;

class YoutubeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $channel = Youtube::getChannelByName('motorgraph');
        $data['channel'] = $channel;

        $videoList = Youtube::getVideoInfo([ $channel->id ]);
        $data['videoList'] = $videoList;

        /*// Get popular videos in a country, return an array of PHP objects
        $popularVideos['ko'] = Youtube::getPopularVideos('kr');
        $popularVideos['us'] = Youtube::getPopularVideos('us');
        $data['popularVideos'] = $popularVideos;*/

        // Get playlist by channel ID, return an array of PHP objects
        //$playlists = Youtube::getPlaylistsByChannelId($channel->id);

        // Specific playlist IDs
        $playlists = [
            (object) [
                "id" => "PLEt63ED1pHgT5vcOQOil8LgLl3mutHaUh",
                "title" => "시승기",
            ],
            (object) [
                "id" => "PLEt63ED1pHgS_rCruLeC0dqAr-UWL2iiz",
                "title" => "롱텀시승기",
            ],
            (object) [
                "id" => "PLEt63ED1pHgRA6ZF7B-g-9ndL_VWUV_ii",
                "title" => "차 VS 차",
            ],
            (object) [
                "id" => "PLEt63ED1pHgRQOQjBz3Nmp_KWr3VOw3sL",
                "title" => "신차발표회",
            ],
            (object) [
                "id" => "PLEt63ED1pHgR3DfbS8rCGmb-bD1keJz8Y",
                "title" => "2015 서울 모터쇼",
            ],
            (object) [
                "id" => "PLEt63ED1pHgTZO286swPNEJOPwb4aTnFb",
                "title" => "2015 상하이 모터쇼",
            ],
            (object) [
                "id" => "PLEt63ED1pHgSAdhnEAHbkn2BfD6ysafEn",
                "title" => "2015 프랑크푸르트 모터쇼",
            ],
            (object) [
                "id" => "PLEt63ED1pHgTUgJeolx5HexS-l-cSkIae",
                "title" => "2015 도쿄 모터쇼",
            ],
        ];

        // Get playlist by ID, return an STD PHP object
        foreach($playlists as $playlist) {
            $playlist = Youtube::getPlaylistById($playlist->id);
            $tmpLists[] = $playlist;
        }
        $data['playlists'] = $tmpLists;

        // Get items in a playlist by playlist ID, return an array of PHP objects
        foreach($data['playlists'] as $playlist) {
            $playlistItems = Youtube::getPlaylistItemsByPlaylistId($playlist->id);
            $data['playlistItems'][] = $playlistItems['results'];
        }

        // 최근 의견
        $commentThreads = Youtube::getCommentThreadsByChannelId($channel->id);
        $data['commentThreads'] = $commentThreads;

        // Get channel activities by channel ID, return an array of PHP objects
        $activities = Youtube::getActivitiesByChannelId($channel->id);
        $data['activities'] = $activities;

        // 최근등록
        $params = [
            "q" => "",
            "type" => "video",
            "part" => "id, snippet",
            "order" => "date",
            "channelId" => $data['channel']->id,
            "maxResults" => 5,
        ];
        $search = Youtube::searchAdvanced($params, true);

        // Check if we have a pageToken
        if (isset($search['info']['nextPageToken'])) {
            $params['pageToken'] = $search['info']['nextPageToken'];
        }

        // Make another call and repeat
        $search = Youtube::searchAdvanced($params, true);
        $data['latests'] = $search['results'];

        // 인기영상
        $params = [
            "q" => "",
            "type" => "video",
            "part" => "id, snippet",
            "order" => "viewcount",
            "channelId" => $data['channel']->id,
            "maxResults" => 5,
        ];
        $search = Youtube::searchAdvanced($params, true);

        // Check if we have a pageToken
        if (isset($search['info']['nextPageToken'])) {
            $params['pageToken'] = $search['info']['nextPageToken'];
        }

        // Make another call and repeat
        $search = Youtube::searchAdvanced($params, true);
        $data['populars'] = $search['results'];

        //dd($commentThreads);
        return view("youtube", $data);
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
        //
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

    /**
     * Get items in a playlist by playlist ID
     *
     * @param $id
     * @return an array of PHP objects
     */
    public function playlistItems($id)
    {
        $data['id'] = $id;

        // Get playlist by ID, return an STD PHP object
        $playlist = Youtube::getPlaylistById($id);
        $data['playlist'] = $playlist;

        // Get items in a playlist by playlist ID, return an array of PHP objects
        $playlistItems = Youtube::getPlaylistItemsByPlaylistId($id);
        $data['playlistItems'] = $playlistItems['results'];

        return view('youtube.items', $data);
    }
}
