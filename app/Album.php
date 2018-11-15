<?php

namespace App;

use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use Taggable;

    protected $table = "albums";
    protected $fillable = ["name", "description", "cover_image", "published_at"];

    /**
     * 앨범에 관계된 이미지들 얻기
     * 앨범은 다수의 이미지들을 가짐 = 일:다 관계 정의
     * param2 = 외래 컬럼
     * param3 = 로컬 컬럼
     * @return mixed
     */
    public function Photos()
    {
        return $this->hasMany('App\Image', 'album_id');
    }

    /**
     * 앨범에 관계된 글들 얻기
     *
     * param2 = 외래 컬럼
     * param3 = 로컬 컬럼
     * @return mixed
     */
    public function Articles()
    {
        return $this->hasMany('App\AlbumArticlesRelationship', 'album_id');
    }
}
