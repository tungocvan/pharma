<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class PermissionModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:module {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'permission for module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $permission = Permission::where('name','=',$name.'-list')->first();

        if($permission){
            $this->info("module $name đã được phân quyền");
        }else{
            $permissionsArray = [
                $name.'-list',
                $name.'-create',
                $name.'-edit',
                $name.'-delete',
             ];
            foreach ($permissionsArray as $permission) {
                Permission::create(['name' => $permission]);
            }
        }


    }
}
