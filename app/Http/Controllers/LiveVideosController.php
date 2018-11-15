<?php

namespace App\Http\Controllers;

use App\Http\Requests\LiveVideosRequest;
use Illuminate\Http\Request;

class LiveVideosController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $OAUTH2_CLIENT_ID = '850366752359-c6delcgpfao8bcsokafpqmkj0m5a7e4k.apps.googleusercontent.com';
        $OAUTH2_CLIENT_SECRET = 'szI462A2-6ak6qTqnR0JDxmi';

        /*
         * You can acquire an OAuth 2.0 client ID and client secret from the
         * Google Developers Console <https://console.developers.google.com/>
         * For more information about using OAuth 2.0 to access Google APIs, please see:
         * <https://developers.google.com/youtube/v3/guides/authentication>
         * Please ensure that you have enabled the YouTube Data API for your project.
         */
        $this->googleClient = new \Google_Client();
        $this->googleClient->setClientId($OAUTH2_CLIENT_ID);
        $this->googleClient->setClientSecret($OAUTH2_CLIENT_SECRET);
        $this->googleClient->setAccessType("offline");
        $this->googleClient->setScopes(['https://www.googleapis.com/auth/youtube']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [
            'isAuth' => false,
            'authUrl' => '',
            'broadcasts' => [],
            'streams' => [],
        ];

        $redirectUrl = url('/livevideos');
        $redirect = filter_var($redirectUrl, FILTER_SANITIZE_URL);
        $this->googleClient->setRedirectUri($redirect);

        if ($request->get('code') !== null) {
            $this->googleClient->authenticate($request->get('code'));
            $token = $this->googleClient->getAccessToken();
            if (isset($token)) $request->session()->put('token', $token);
        }

        if ($request->session()->has('token')) {
            $token = $request->session()->get('token');
            if (gettype($token) == "array") {
                $this->googleClient->setAccessToken($token);
            }
        }

        //TODO: check token expired and if expired refresh token and set access token to the google client class

        if ($this->googleClient->getAccessToken()) {
            if ($this->googleClient->isAccessTokenExpired()) {
                $request->session()->forget('token');
                $request->session()->put('state', '구글 인증 토큰이 만료. 다시 인증을 받아주십시오.');
            } else {
                $data['isAuth'] = true;

                // Define an object that will be used to make all API requests.
                $youtube = new \Google_Service_YouTube($this->googleClient);

                try {
                    // Execute an API request that lists broadcasts owned by the user who
                    // authorized the request.
                    $broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
                        'id,snippet,status,contentDetails',
                        [
//                            'broadcastStatus' => 'all',   // Filter - 방송상태: active|all|completed|upcoming
                            'mine' => 'true',   // Request API 중 Filters 쓰려면 주석처리
                        ]
                    );

                    $data['broadcasts'] = $broadcastsResponse['items'];

                    // 방송 처음 항목
                    $i = 0;
                    $broadcast = $broadcastsResponse['items'][$i];

                    // UTC to KST
                    $dt = new \DateTime($broadcast->getSnippet()->getScheduledStartTime(), new \DateTimeZone('Asia/Seoul'));
                    $dt->setTimezone(new \DateTimeZone('KST'));
                    $data['broadcasts'][$i]['snippet']['scheduledStartTime'] = $dt->format('Y-m-d H:i:s');

                    $dt = new \DateTime($broadcast->getSnippet()->getScheduledEndTime(), new \DateTimeZone('Asia/Seoul'));
                    $dt->setTimezone(new \DateTimeZone('KST'));
                    $data['broadcasts'][$i]['snippet']['scheduledEndTime'] = $dt->format('Y-m-d H:i:s');

                    // 스트림 아이디 조회
                    $streamId = $broadcast->getContentDetails()->getBoundStreamId();

                    // 문서 https://api.kdyby.org/class-Google_Service_YouTube_LiveStreams_Resource.html
                    $streamsResponse = $youtube->liveStreams->listLiveStreams(
                        'id,snippet,cdn,status',
                        [
                            'id' => $streamId,      // Filter - Stream Id
//                            'mine' => 'true',   // Request API 중 Filters 쓰려면 주석처리
                        ]
                    );

                    // 스트림 목록 조회
                    // https://api.kdyby.org/class-Google_Service_YouTube_LiveStreamListResponse.html
                    $streams = $streamsResponse->getItems();

                    $stream = $streams[$i];
                    // 스트림 인코더 정보 조회
                    $ingestionInfo = $stream->getCdn()->getIngestionInfo();
                    // 템플릿에서 방송 목록 루프의 하위 요소로 구할 수 있도록 할당
                    $data['broadcasts'][$i]['streams'] = [
                        'name' => $ingestionInfo->getStreamName(),
                        'url' => $ingestionInfo->getIngestionAddress(),
                        'backupUrl' => $ingestionInfo->getBackupIngestionAddress(),
                    ];

                } catch (\Google_Service_Exception $e) {
                    $request->session()->put('state', sprintf('<p>A service error occurred on youtube: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
                } catch (\Google_Exception $e) {
                    $request->session()->put('state', sprintf('<p>An client error occurred on youtube: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
                }
            }
        } else {
            if ($request->session()->has('token')) {
                $request->session()->forget('token');
            }

            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $this->googleClient->setState($state);
            $request->session()->put('state', $state);

            $data['authUrl'] = $this->googleClient->createAuthUrl();
        }

        return view('liveVideos.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            "id" => "",
        ];

        return view('liveVideos.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LiveVideosRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(LiveVideosRequest $request)
    {
        $title = $request->get('title');
        $description = $request->get('description');
        $scheduledStartTime = $request->get('scheduledStartTime');
        $scheduledEndTime = $request->get('scheduledEndTime');

        $scheduledStartTime = date('c', strtotime($scheduledStartTime)); // +09:00 과 같은 KST 날짜시간 반환. youtube 등록 시간은 아래와 같음.
        //$scheduledStartTime = gmdate('c', strtotime($scheduledStartTime)); // +00:00 과 같은 UTC 날짜시간 반환. youtube 등록 시간은 위와 같음.

        $scheduledEndTime = date('c', strtotime($scheduledEndTime)); // +09:00 과 같은 KST 날짜시간 반환. youtube 등록 시간은 아래와 같음.
        //$scheduledEndTime = gmdate('c', strtotime($scheduledEndTime)); // +00:00 과 같은 UTC 날짜시간 반환. youtube 등록 시간은 위와 같음.

        $redirectUrl = url('/livevideos');
        if (strpos($redirectUrl, '.local') !== false) $redirectUrl = str_replace('.local', '.com', $redirectUrl);
        $redirect = filter_var($redirectUrl, FILTER_SANITIZE_URL);
        $this->googleClient->setRedirectUri($redirect);

        // extract token from session and configure client
        if ($request->session()->has('token')) {
            $token = $request->session()->get('token');
            $this->googleClient->setAccessToken($token);
        } else {
            redirect('/livevideos/create')->withInput();
        }

        $htmlBody = '';

        if ($this->googleClient->isAccessTokenExpired()) {
            $request->session()->forget('token');
            $htmlBody = '구글 인증 토큰이 만료. 다시 인증을 받아주십시오.';
            redirect('/livevideos/create')->with('status', $htmlBody)->withInput();
        }

        // Check to ensure that the access token was not acquired.
        if (!$this->googleClient->getAccessToken()) {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $this->googleClient->setState($state);
            $request->session()->put('state', $state);

            $authUrl = $this->googleClient->createAuthUrl();

            if (strpos($authUrl, '.local') !== false) $authUrl = str_replace('.local', '.com', $authUrl);

            $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
  <p>계정 선택 목록이 보이면 <strong>빨간 아이콘 모터그래프 motorgraph</strong> 선택!</p>
END;
            return redirect('/livevideos/create')->with('status', $htmlBody)->withInput();
        }

        // Define an object that will be used to make all API requests.
        $youtube = new \Google_Service_YouTube($this->googleClient);

        try {
            // Create an object for the liveBroadcast resource's snippet. Specify values
            // for the snippet's title, scheduled start time, and scheduled end time.
            $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
            $broadcastSnippet->setTitle($title);
            $broadcastSnippet->setDescription($description);
            $broadcastSnippet->setScheduledStartTime($scheduledStartTime);
            if ($scheduledEndTime) $broadcastSnippet->setScheduledEndTime($scheduledEndTime);

            // Create an object for the liveBroadcast resource's status, and set the
            // broadcast's status to "private".
            // https://developers.google.com/youtube/v3/live/docs/liveBroadcasts#status.privacyStatus
            $status = new \Google_Service_YouTube_LiveBroadcastStatus();
            $status->setPrivacyStatus($request->get('privacyStatus'));

            // Create the API request that inserts the liveBroadcast resource.
            $broadcastInsert = new \Google_Service_YouTube_LiveBroadcast();
            $broadcastInsert->setSnippet($broadcastSnippet);
            $broadcastInsert->setStatus($status);
            $broadcastInsert->setKind('youtube#liveBroadcast');

            // Execute the request and return an object that contains information
            // about the new broadcast.
            $broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status', $broadcastInsert, []);

            // Create an object for the liveStream resource's snippet. Specify a value
            // for the snippet's title.
            $streamSnippet = new \Google_Service_YouTube_LiveStreamSnippet();
            $streamSnippet->setTitle($title);
            $streamSnippet->setDescription($description);

            // Create an object for content distribution network details for the live
            // stream and specify the stream's format and ingestion type.
            $cdn = new \Google_Service_YouTube_CdnSettings();
            $cdn->setResolution($request->get('cdnResolution'));
            $cdn->setFrameRate($request->get('cdnFrameRate'));
            $cdn->setIngestionType('rtmp');

            // Create the API request that inserts the liveStream resource.
            $streamInsert = new \Google_Service_YouTube_LiveStream();
            $streamInsert->setSnippet($streamSnippet);
            $streamInsert->setCdn($cdn);
            $streamInsert->setKind('youtube#liveStream');

            // Execute the request and return an object that contains information about the new stream.
            $streamsResponse = $youtube->liveStreams->insert('snippet,cdn', $streamInsert, []);

            // Bind the broadcast to the live stream.
            $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
                $broadcastsResponse['id'], 'id,contentDetails',
                [
                    'streamId' => $streamsResponse['id'],
                ]
            );

            $htmlBody .= "<h3>Added Broadcast</h3><ul>";
            $htmlBody .= sprintf('<li>%s published at %s (%s)</li>',
                $broadcastsResponse['snippet']['title'],
                $broadcastsResponse['snippet']['publishedAt'],
                $broadcastsResponse['id']);
            $htmlBody .= '</ul>';

            $htmlBody .= "<h3>Added Stream</h3><ul>";
            $htmlBody .= sprintf('<li>%s (%s)</li>',
                $streamsResponse['snippet']['title'],
                $streamsResponse['id']);
            $htmlBody .= '</ul>';

            $htmlBody .= "<h3>Bound Broadcast</h3><ul>";
            $htmlBody .= sprintf('<li>Broadcast (%s) was bound to stream (%s).</li>',
                $bindBroadcastResponse['id'],
                $bindBroadcastResponse['contentDetails']['boundStreamId']);
            $htmlBody .= '</ul>';

        } catch (\Google_Service_Exception $e) {
            $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        } catch (\Google_Exception $e) {
            $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        }

        session(['token', $this->googleClient->getAccessToken()]);

        return redirect('/livevideos')->with('status', $htmlBody)->withInput();
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
        $data = [
            "id" => $id,
        ];

        return view('liveVideos.form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LiveVideosRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(LiveVideosRequest $request, $id)
    {
        // 수정 처리 유효성 검사 추가
        $this->validate($id, [
            'id' => 'required|numeric'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($request->session()->has('token')) {
            $token = $request->session()->get('token');
            $this->googleClient->setAccessToken($token);
        } else {
            $htmlBody = '구글 인증을 받아주십시오.';
            redirect('/livevideos')->with('status', $htmlBody);
        }

        $htmlBody = '';

        if ($this->googleClient->isAccessTokenExpired()) {
            $request->session()->forget('token');
            $htmlBody = '구글 인증 토큰 만료. 다시 인증을 받아주십시오.';
            redirect('/livevideos')->with('status', $htmlBody);
        }

        // Check to ensure that the access token was not acquired.
        if (!$this->googleClient->getAccessToken()) {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $this->googleClient->setState($state);
            $request->session()->put('state', $state);

            $authUrl = $this->googleClient->createAuthUrl();

            if (strpos($authUrl, '.local') !== false) $authUrl = str_replace('.local', '.com', $authUrl);

            $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
  <p>계정 선택 목록이 보이면 <strong>빨간 아이콘 모터그래프 motorgraph</strong> 선택!</p>
END;
            return redirect('/livevideos')->with('status', $htmlBody);
        }

        // Define an object that will be used to make all API requests.
        $youtube = new \Google_Service_YouTube($this->googleClient);
        $liveBroadcasts = $youtube->liveBroadcasts;

        try {
            // 문서 https://api.kdyby.org/class-Google_Service_YouTube_LiveBroadcasts_Resource.html#_delete
            $liveBroadcasts->delete($id);

            $htmlBody .= "<h3>Deleted Broadcast</h3>";

            try {
                // Execute an API request that lists broadcasts owned by the user who
                // authorized the request.
                $broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
                    'id,snippet,status,contentDetails',
                    [
                        'id' => $id,
                        'mine' => 'true',
                    ]
                );

                $broadcast = $broadcastsResponse['items'][0];
                $streamId = $broadcast->getContentDetails()->getBoundStreamId();

                // 문서 https://api.kdyby.org/class-Google_Service_YouTube_LiveStreams_Resource.html#_delete
                $liveStreams = $youtube->liveStreams;
                $liveStreams->delete($streamId);

                $htmlBody .= "<h3>Deleted Stream</h3>";
            } catch (\Google_Service_Exception $e) {
                $request->session()->put('state', sprintf('<p>A service error occurred on youtube: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
            } catch (\Google_Exception $e) {
                $request->session()->put('state', sprintf('<p>An client error occurred on youtube: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
            }
        } catch (\Google_Service_Exception $e) {
            $htmlBody .= sprintf('<p>A service error occurred on youtube: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        } catch (\Google_Exception $e) {
            $htmlBody .= sprintf('<p>An client error occurred on youtube: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        }

        return redirect('/livevideos')->with('status', $htmlBody);
    }
}
