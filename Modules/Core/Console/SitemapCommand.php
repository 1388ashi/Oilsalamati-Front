<?php

namespace Modules\Core\Console;

use Shetabit\Shopit\Modules\Core\Console\SitemapCommand as BaseSitemapCommand;


use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Post;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopit:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = config('app.front_url');
        $site_address = app(CoreSettings::class)->get('sitemap_address') ?:
            app(CoreSettings::class)->get('sitemap.address');
        $posts = Post::query()->select(['id', 'updated_at', 'slug'])->get();
        $products = Product::query()->select(['id', 'updated_at', 'title'])->get();
        $categories = Category::query()->select(['id', 'updated_at', 'slug'])->get();
        $sitemap = $this->getSitemap($url);

        $this->productShowSitemap($sitemap, $products, $url);
        $this->productIndexSitemap($sitemap, $categories, $url);
        $this->weblogListSitemap($sitemap, $posts, $url);

        $sitemap->writeToFile($site_address);
    }

    protected function getSitemap($url) {
        return Sitemap::create()
            ->add(Url::create('/')->setUrl($url.'/')
                ->setLastModificationDate(now())->setPriority(1));
    }

    protected function productShowSitemap($sitemap, $products, $url)
    {
        $coreSetting = app(\Modules\Core\Classes\CoreSettings::class);
        $key = $coreSetting->get('sitemap.route_maps.product_show', 'product');
        foreach ($products as $product) {
            $sitemap->add(Url::create('/')->setUrl($url."/$key/{$product->id}/{$product->slug}")
                ->setLastModificationDate($product->updated_at)->setPriority(1));
        }
    }

    protected function productIndexSitemap($sitemap, $categories, $url) {
        $coreSetting = app(\Modules\Core\Classes\CoreSettings::class);
        $key1 = $coreSetting->get('sitemap.route_maps.product_index', 'products');

        $sitemap->add(Url::create('/')->setUrl($url."/$key1")->setLastModificationDate(now())->setPriority(1))
            ->add(Url::create('/')->setUrl($url."/$key1?sort=top_sales")->setLastModificationDate(now())->setPriority(0.8))
            ->add(Url::create('/')->setUrl($url."/$key1?sort=low_to_high")->setLastModificationDate(now())->setPriority(0.8))
            ->add(Url::create('/')->setUrl($url."/$key1?sort=most_visited")->setLastModificationDate(now())->setPriority(0.8))
            ->add(Url::create('/')->setUrl($url."/$key1?sort=newest")->setLastModificationDate(now())->setPriority(0.8))
            ->add(Url::create('/')->setUrl($url."/$key1?sort=most_discount")->setLastModificationDate(now())->setPriority(0.8));

        $key2 = $coreSetting->get('sitemap.route_maps.product_category', 'products');
        foreach ($categories as $category) {
            $sitemap->add(Url::create('/')->setUrl($url."/$key2/{$category->id}/{$category->slug}")
                ->setLastModificationDate($category->updated_at)->setPriority(0.8));
        }
    }

    protected function weblogListSitemap($sitemap, $posts, $url)
    {
        $coreSetting = app(\Modules\Core\Classes\CoreSettings::class);
        $key1 = $coreSetting->get('sitemap.route_maps.post_index', 'weblog-list');
        $key2 = $coreSetting->get('sitemap.route_maps.post_show', 'weblog-list');

        $sitemap->add(Url::create('/')->setUrl($url."/$key1")->setLastModificationDate(now())->setPriority(0.5));
        foreach ($posts as $post) {
            $sitemap->add(Url::create("/")->setUrl($url."/$key2/{$post->id}/{$post->slug}")
                ->setLastModificationDate($post->updated_at)->setPriority(0.5));
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
