<?php

namespace App\Http\Controllers;

use Alaouy\Youtube\Facades\Youtube;
use Illuminate\Http\Request;
use willvincent\Feeds\Facades\FeedsFacade;

class CommentsController extends Controller
{
    public $accessToken = "EAAMGdYD5UVgBALZApSmHSNId9WObUJqrXK3txVAr7PafQBS3jEjUTC1G7KAdWZAStu7JCH9glgFsGskMP1MDT7BYVwj3ZCBoiONaAxo6KUCntiBdJcGeqe7scZBjjYyYLKQtTGJmpZApS8Ulql1on345SS8fdrNAWpe0kpnZCO9AZDZD";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];

        $data = $this->getNewsFacebookCommentsGroupByNews();

        // Youtube 의견
        // 채널명으로 채널 정보 로딩
        $channel = Youtube::getChannelByName('motorgraph');
        $data['youtube']['channel'] = $channel;

        // 최근 의견
        $commentThreads = Youtube::getCommentThreadsByChannelId($channel->id);
        $data['youtube']['commentThreads'] = $commentThreads;

        return view('comments.index', $data);
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
     * Facebook open graph objects via permalink(s)
     *
     * @param $ids CSV format
     * @return mixed array
     *
     * @description
     * https://developers.facebook.com/docs/graph-api/reference/v2.7/url/
     */
    public function getFacebookOpenGraphObjectsByUrl($ids)
    {
        $url = "https://graph.facebook.com/v2.7/?access_token={$this->accessToken}&ids={$ids}";
        $response = @file_get_contents($url);

        if (!$response) return [];

        $json = json_decode($response, true);

        return $json;
    }

    /**
     * Facbook Comment Count by permalink
     *
     * @param $ogObject (Open Graph Object)
     * @return int
     */
    public function getNewsFacebookCommentCount($ogObject)
    {
        return $ogObject['share']['comment_count'];
    }

    /**
     * Facbook Comments by permalink
     *
     * @param $id Facebook open graph object id
     * @return mixed array
     *
     * @description
     * https://developers.facebook.com/docs/graph-api/reference/v2.7/object/comments/
     * http://www.php-developer.org/facebook-comments-api-using-php-and-json-complete-working-example/
     */
    public function getNewsFacebookComments($id)
    {
        $items = [];

        // 필수 값 검사
        if (!$id) return $items;

        // Comment 조회 API
        $url = "https://graph.facebook.com/v2.7/{$id}/comments/?access_token={$this->accessToken}&order=reverse_chronological&total_count=200";
        $response = @file_get_contents($url);
        if (!$response) return $items;

        $json = json_decode($response, true);

        $items = $json['data'];

        return $items;
    }

    /**
     * Get MG news recent comments group by news article
     *
     * @return mixed array
     */
    public function getNewsFacebookCommentsGroupByNews()
    {
        $data = [];

        // 뉴스 사이트 최근 의견 : Facebook comment plugin
        // 전체기사 RSS Feed
        $url = "http://www.motorgraph.com/rss/allArticle.xml";
        $feed = FeedsFacade::make($url);
        $feeds = [
            'title' => $feed->get_title(),
            'permalink' => $feed->get_permalink(),
            'items' => $feed->get_items(),
        ];

        $ids = [];
        $permalinks = [];
        $titles = [];
        $descriptions = [];
        $contents = [];
        $data['facebook']['commentThreads'] = [];
        foreach($feeds['items'] as $item) {
            $permalink = $item->get_permalink();
            $title = $item->get_title();
            $description = $item->get_description();
            $content = $item->get_content();
            $author = $item->get_author(); // $author->name, $author->link, $author->email
            $date = $item->get_date("Y-m-d h:i:s");

            // Facebook API 호출 수 제한이 존재하므로 Batch 호출 위해 배열에 push
            $permalinks[] = $permalink;
        }

        if (count($permalinks) < 1) return $data;

        $json = $this->getFacebookOpenGraphObjectsByUrl(implode(",", $permalinks));

        foreach($permalinks as $permalink) {
            $ogObject = $json[$permalink];

            // 의견 수 확인
            $commentCount = $this->getNewsFacebookCommentCount($ogObject);
            // 의견 수가 0개이면 현 루프 항목 건너뛰기
            if ($commentCount < 1) continue;

            $title = html_entity_decode($ogObject['og_object']['title']);
            $description = html_entity_decode($ogObject['og_object']['description']);

            // 의견들 로딩
            $comments = $this->getNewsFacebookComments($ogObject['og_object']['id']);

            $data['facebook']['commentThreads'][] = [
                'comments' => $comments,
                'link' => $ogObject['id'],
                'title' => $title,
            ];
        }

        return $data;
    }

    /**
     * Get MG news recent comments order by desc
     *
     * @return mixed array
     */
    public function getNewsFacebookCommentsOrderByDesc()
    {
        $data = [];

        // 뉴스 사이트 최근 의견 : Facebook comment plugin
        // 전체기사 RSS Feed
        $url = "http://www.motorgraph.com/rss/allArticle.xml";
        $feed = FeedsFacade::make($url);
        $feeds = [
            'title' => $feed->get_title(),
            'permalink' => $feed->get_permalink(),
            'items' => $feed->get_items(),
        ];

        $ids = [];
        $data['facebook']['commentThreads'] = [];
        foreach($feeds['items'] as $item) {
            $permalink = $item->get_permalink();
            $title = $item->get_title();
            $description = $item->get_description();
            $content = $item->get_content();
            $author = $item->get_author(); // $author->name, $author->link, $author->email
            $date = $item->get_date("Y-m-d h:i:s");

            // 의견 수 확인
            $commentCount = $this->getNewsFacebookCommentCount($permalink);
            // 의견 수가 0개이면 다음~
            if ($commentCount < 1) continue;

            // Facebook API 호출 수 제한이 존재하므로 Batch 호출 위해 배열에 push
            $ids[] = $permalink;
        }

        // 의견들 로딩
        $csvIds = implode(",", $ids);
        $comments = $this->getNewsFacebookComments($csvIds);
        // 각 의견들에 부모글 정보 추가 $i = 글들, $j = 글의 의견들
        for($i = 0; $i < count($comments); $i++) {
            $comment['parent'] = [
                'link' => $permalink,
                'title' => $title
            ];

            for($j = 0; $j < count($comments[$i]); $j++) {
                $comments[$i][$j] = array_merge($comments[$i][$j], $comment);
            }

            // View data 의견 리스트 배열에 쌓기. 시간순 정렬 위해 2차원 배열을 1차원 배열로.
            $data['facebook']['commentThreads'] = array_merge($data['facebook']['commentThreads'], $comments[$i]);
        }

        // 시간 역순 정렬
        usort($data['facebook']['commentThreads'], function($a, $b) {
            $aTime = strtotime($a['created_time']);
            $bTime = strtotime($b['created_time']);

            if ($aTime == $bTime) return 0;

            return ($aTime < $bTime)? 1 : -1;
        });

        return $data;
    }
}