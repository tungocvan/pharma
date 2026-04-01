<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use Illuminate\Support\Facades\Artisan;

class CreateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:module {name} {--delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = ucfirst($this->argument('name'));
        $option = $this->option('delete');

        $pathModule = base_path('Modules/' . $name);
        if($option){
            if (File::exists($pathModule)) {
                ///dd($pathModule);
                File::deleteDirectory($pathModule);
                $this->info('Module đã được xóa thành công.');
                return 0;

            }else{
                $this->info('Module khong ton tai.');
            }
        }

        if (File::exists($pathModule)) {
            $this->error('Module da ton tai');
        }else{
            $this->info('Module đã được tạo');
            $moduleDir = [
                $name,
                $name.'/config',
                $name.'/database',
                $name.'/database/factories',
                $name.'/database/migrations',
                $name.'/database/Seeders',
                $name.'/Helpers',
                $name.'/Http',
                $name.'/Http/Controllers',
                $name.'/Http/Controllers/Api',
                $name.'/Http/Middleware',
                $name.'/Http/Requests',
                $name.'/Http/Components',
                $name.'/Livewire',
                $name.'/Models',
                $name.'/resources',
                $name.'/resources/sass',
                $name.'/resources/css',
                $name.'/resources/js',
                $name.'/resources/lang',
                $name.'/resources/views',
                $name.'/resources/views/pages',
                $name.'/resources/views/livewire',
                $name.'/resources/views/components',
                $name.'/Services',
                $name.'/routes'
            ];
            foreach ($moduleDir as $value) {
                File::makeDirectory(base_path('Modules/' . $value), 0755, true, true);
            }

            Artisan::call('create:model', [
                'model' => strtolower($name),
                'module' => strtolower($name)
            ]);

            Artisan::call('create:controller', [
                'controller' => strtolower($name),
                'module' => strtolower($name)
            ]);
            Artisan::call('create:routes', [
                'name' => strtolower($name)
            ]);
            Artisan::call('permission:module', [
                'name' => strtolower($name)
            ]);

        }
    }
}
