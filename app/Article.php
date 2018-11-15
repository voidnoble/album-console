<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = "article";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'externUid'
        ,'externTypeCode'
        ,'externType2Code'
        ,'title'
        ,'body'
        ,'occurDate'
        ,'url'
        ,'bodyUpdateDate'
        ,'externOccurDate'
        ,'externOccur2Date'
        ,'nickname'
        ,'reCnt'
        ,'oppositCnt'
        ,'hitCnt'
        ,'originSource'
        ,'typeCode'
        ,'type2Code'
        ,'externNickname'
        ,'externRecCnt'
        ,'externOppositCnt'
        ,'externHitCnt'
        ,'externOriginSource'
        ,'rawBody'
        ,'publRawBody'
        ,'publStatus'
        ,'publDate'
        ,'writerUid'
        ,'writerTypeCode'
        ,'delStatus'
        ,'delDate'
        ,'siteUid'
        ,'subTitle'
        ,'miniTitle'
        ,'keywordsStr'
        ,'externUrl'
        ,'externBody'
        ,'writerEmail'
        ,'primeOneDepthCateItemUid'
        ,'makerUsrTypeCode'
        ,'makerUsrUid'
        ,'parentMdModelName'
        ,'parentMdUid'
        ,'modifyDate'
        ,'primeGroupCodesStr'
        ,'publReadyStatus'
        ,'externPublStatus'
        ,'externOccur3Date'
        ,'externDelStatus'
        ,'title2'
        ,'writerName'
        ,'externType3Code'
        ,'sData'
        ,'movieCode'
        ,'bodyOrigin'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}
