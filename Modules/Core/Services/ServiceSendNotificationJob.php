<?php

namespace Modules\Core\Services;

//use Shetabit\Shopit\Modules\Core\Services\ServiceSendNotificationJob as BaseServiceSendNotificationJob;


use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Order\Entities\Order;

class ServiceSendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $tokens;
    public string $title;
    public string $body;
    public $byModel;
    public $forModel;
    public int|array  $forTheKeys;
    public array  $sendingMethods;
    public string $link;
    public string $image;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $title, $body, $byModel,
        $forModel, $forTheKeys = [],
        $link = '', $image = '',
        $sendingMethods = ['firebase','mail','database']
    ){
        $this->title = $title;
        $this->body = $body;
        $this->byModel = $byModel;
        $this->forModel = $forModel;
        $this->forTheKeys = $forTheKeys;
        $this->sendingMethods = $sendingMethods;
        $this->link = $link;
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        #array['firebase','mail','database']
        foreach ($this->sendingMethods as $sendingMethod) {
            if (is_array($this->byModel) || is_object($this->byModel)){
                foreach ($this->byModel as $model) {
                    $this->$sendingMethod($model);
                }
            }else{
                $this->$sendingMethod($this->byModel);
            }
        }
    }

    protected function firebase($byModel)
    {
        $className = strtolower(class_basename($this->byModel));
        $tokens = $this->getTokens($this->getIds(), $this->forModel);
        if (empty($tokens)) {
            return;
        }
        $message =  (new FirebaseMessage())
            ->withTitle($this->title)
            ->withBody($this->body)
            ->withClickAction($className.'/'.$byModel->id);

        if ($this->image) {
            $message->withImage($this->image);
        }
        $message->asNotification(array_values(array_unique($tokens)));
    }

    protected function mail($byModel)
    {
        $emails = $this->getEmail($this->getAvailableUser($this->forTheKeys));
        if (empty($emails)){
            return;
        }

        Log::info('email send');
//    \Mail::to($emails)->send(new ListenChargeMail($this->title));
    }

    protected function database($byModel)
    {
        /** @var $byModel Order  */
        $ids = $this->getIds();
        $className = strtolower(class_basename($byModel));
        $userClassName = strtolower(class_basename($this->forModel));
        if ($byModel instanceof Order) {

        }

        if($byModel->$userClassName != null){
            DatabaseNotification::query()->create([
                'id' => \Str::uuid(),
                'type' => $className,
                'notifiable_type' => $this->forModel,
                'notifiable_id' =>  $byModel->$userClassName->id,
                'data' =>  [
                    $className.'_id' => $byModel->id,
                    'title' => $this->title,
                    'description' => $this->body,
                ],
                'read_at' =>  null,
                'created_at' =>  now(),
                'updated_at' =>  now(),
            ]);
        }else {
            foreach ($ids as $id) {
                DatabaseNotification::query()->create([
                    'id' => \Str::uuid(),
                    'type' => $className,
                    'notifiable_type' => $this->forModel,
                    'notifiable_id' => $id,
                    'data' => [
                        $className . '_id' => $byModel->id,
                        'title' => $this->title,
                        'description' => $this->body,
                    ],
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function getAvailableUser($keys)
    {
        return $this->forModel::query()
            ->select(['id', 'email'])
            ->when(!empty($keys) && is_array($keys), function ($query) use ($keys){
                $query->whereIn('id', $keys);
            })
            ->when(!empty($keys) && is_numeric($keys), function ($query) use ($keys){
                $query->whereKey($keys);
            })->get();
    }

    public function getIds()
    {
        $users = $this->getAvailableUser($this->forTheKeys);

        return $users->pluck('id')->toArray();

    }

    public function getTokens($ids, $model)
    {
        return  DB::table('personal_access_tokens')
            ->where('tokenable_type', $model)
            ->whereNotNull('device_token')
            ->whereIn('tokenable_id', $ids)
            ->get('device_token')->pluck('device_token')->toArray();
    }

    public function getEmail($user)
    {
        return $user->whereNotNull('email')->pluck('email')->toArray();
    }
}
