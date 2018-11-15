<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommentsController;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @property CommentsController commentsController
 */
class NotifyComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "comments:notify {what?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * @var CommentsController
     */
    protected $commentsController;

    /**
     * Create a new command instance.
     */
    public function __construct(CommentsController $commentsController)
    {
        parent::__construct();

        $this->commentsController = $commentsController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $what = $this->argument('what');

        switch ($what) {
            case "news":
                $this->sendNewsFacebookCommentsToSlack();
                break;
            default:
                //Log::info("Invalid argument on NotifyComments::handle().");
                break;
        }
    }

    /**
     * Send recent Facebook comments to MG Slack
     *
     * @return bool
     */
    public function sendNewsFacebookCommentsToSlack()
    {
        $data = $this->commentsController->getNewsFacebookCommentsGroupByNews();
        if (!isset($data)) return false;

        $now = Carbon::now()->toDateTimeString();
        $text = "*[{$now}] 자사 뉴스 사이트 최근 의견*\n\n";

        for ($i = 0; $i < count($data['facebook']['commentThreads']); $i++) {
            $li = $data['facebook']['commentThreads'][$i];

            $idx = $i + 1;

            $link = $li['link'];
            $title = $li['title'];

            $commentsText = "";

            for ($j = 0; $j < count($li['comments']); $j++) {
                $comment = $li['comments'][$j];

                $userName = $comment['from']['name'];
                $regdate = date("Y-m-d H:i:s", strtotime($comment['created_time']));
                $content = $comment['message'];

                $commentsText .= "```• {$userName} ({$regdate})\n{$content}```\n";
            }

            $text .= "{$idx}. {$title} ({$link})\n{$commentsText}\n\n";
        }

        //$channel = (app()->isLocal())? "development" : "general"; // 구분하지 못하여 주석처리함
        $channel = "general";
        $url = "https://motorgraph.slack.com/services/hooks/slackbot?token=Vwg8lTaNfoVNjuvcLOv9Gn61&channel=%23{$channel}";
        $this->sendToSlack($url, $text);
    }

    /**
     * Send recent Facebook comments to MG Slack
     *
     * @param $url
     * @param $text
     */
    public function sendToSlack($url, $text)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
        curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        // in real life you should use something like:
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('postvar1' => 'value1')));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        // further processing ....
        //if ($server_output == "OK") { ... } else { ... }
    }
}
