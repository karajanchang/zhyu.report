<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2019-03-02
 * Time: 05:36
 */

namespace ZhyuReport;

use Illuminate\Support\ServiceProvider;
use ZhyuReport\Commands\MakeReportCommand;

use ZhyuReport\Report\Media\CsvReport;
use ZhyuReport\Report\Media\ExcelReport;
use ZhyuReport\Report\Media\PdfReport;
use ZhyuReport\Report\ReportFactory;
use ZhyuReport\Report\ReportService;


class ZhyuServiceProvider extends ServiceProvider
{
    protected $commands = [
        MakeReportCommand::class,
    ];

    public function register(){
        $this->loadFunctions();

        $this->app->bind('csv.report', function ($app) {

            return new CsvReport ($app);
        });

        $this->app->bind('excel.report', function ($app) {

            return new ExcelReport ($app);
        });

        $this->app->bind('pdf.report', function ($app) {

            return new PdfReport ($app);
        });

        $this->app->bind('ZhyuReport', function($app, array $params)
        {
            if(!isset($params['name']) || strlen($params['name'])==0){
                throw new \Exception('Please provider one class for report to bind');
            }
            ReportFactory::bind($params['name']);
            $service = $app->make(ReportService::class);

            return $service;
        });


        $configPath = __DIR__.'/config/report-generator.php';
        $this->mergeConfigFrom($configPath, 'zhyu');


        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');

        $this->registerAliases();
    }

    public function boot(){
        if ($this->isLumen()) {
            require_once 'Lumen.php';
        }else {

            if(env('ZHYU_USE_ADMIN_FUNCTIONS', false) === true) {



                $this->publishes([
                    __DIR__ . '/config/report-generator.php' => config_path('report-generator.php'),
                ], 'zhyu');

            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    protected function loadFunctions(){
        foreach (glob(__DIR__.'/functions/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Zhyu\ZhyuServiceProvider::class,
        ];
    }

    /**
     * Register aliases.
     *
     * @return null
     */
    protected function registerAliases()
    {
        if (class_exists('Illuminate\Foundation\AliasLoader')) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            $loader->alias('CsvReport', \Zhyu\Facades\CsvReport::class);
            $loader->alias('ExcelReport', \Zhyu\Facades\ExcelReport::class);
            $loader->alias('PdfReport', \Zhyu\Facades\PdfReport::class);
        }
    }

    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen');
    }
}
