<?php

namespace Modules\Setting\Http\Traits;

//trait PushToConfig
//{
//    use \Shetabit\Shopit\Modules\Setting\Http\Traits\PushToConfig;
//}



use Carbon\Carbon;
use Modules\Setting\Entities\Setting;

trait PushToConfig
{

    protected $configPath = __DIR__.'/../../Config/config.php';

    public function getInDatabase()
    {
        $settings = Setting::query()->get();
        $configSettings = '';
        $new = Carbon::now()->toDateTimeString();

        foreach ($settings as $setting){
            $configSettings .= "
        [
            'group'   => '$setting->group',
            'label'   => '$setting->label',
            'name'   => '$setting->name',
            'type'   => '$setting->type',
            'value'   => '$setting->value',
            'options' => '$setting->options',
            'private'   => '$setting->private',
            'created_at' => '$new',
            'updated_at' => '$new'
        ],";
        }

        return ($configSettings);

    }

    public function push()
    {
        $baseFile ="<?php"." \n\n return [ \n\t 'name' => 'Setting', \n\t 'settings' =>[";
        file_put_contents($this->configPath, $baseFile.$this->getInDatabase().PHP_EOL."  ]\n];");
    }


    public function run()
    {
        $this->push();
    }
}
