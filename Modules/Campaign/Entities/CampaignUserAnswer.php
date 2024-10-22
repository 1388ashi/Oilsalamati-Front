<?php

namespace Modules\Campaign\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class CampaignUserAnswer extends Model
{
    protected $fillable = [
        'question_id','answer','user_id',
    ];

    protected $appends= [
        'mobile','question_title'
    ];

    public function getMobileAttribute()
    {
        return $this->user->mobile;
    }

    public function getQuestionTitleAttribute()
    {
        return $this->question->question;
    }

    public function question()
    {
        return $this->belongsTo(CampaignQuestion::class,'question_id');
    }
    public function user()
    {
        return $this->belongsTo(CampaignUser::class,'user_id');
    }

    public static function showAnswerByKey($question_id,$type,$data)
    {
        $question = CampaignQuestion::findOrFail($question_id);

        if ($data){
            if ($type == 'text'){
                return $data;
            }
            if ($type == 'options'){
                return json_decode($question->data)->$data;
            }
            if ($type == 'checkbox'){
                $answers= explode(',',$data);
                foreach ($answers as $answer){
                    $res[]= json_decode($question->data)->$answer;
                }

                return implode(',',$res);
            }
        }
    }
}
