<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlbumArticlesRelationship extends Model
{
    protected $table = 'album_articles_relationship';
    protected $fillable = ['album_id', 'article_id', 'title', 'url', 'order'];
    public $timestamps = true;   // created_at, updated_at 필드 자동 입력

    /**
     * 앨범에 관계된 글들 얻기
     *
     * param2 = 외래 컬럼
     * param3 = 로컬 컬럼
     * @return mixed
     */
    public function Articles()
    {
        return $this->hasMany('App\Article', 'uid', 'article_id');
    }
}
