<?php
 
namespace Modules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
       
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $this->registerModule($module);
        }
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    private function getModules(): array
    {
        // Lấy danh sách các folder trong thư mục Modules
        $path = __DIR__;
        return array_map('basename', File::directories($path));
    }

    private function registerModule($module)
    {
        $modulePath = __DIR__ . "/{$module}";
        $moduleNameLower = strtolower($module); // vd: admin

        // --- 1. CONFIG (Load trước để các phần khác có thể dùng config) ---
        // Hỗ trợ cả folder 'Config' và 'config'
        $configPath = File::exists($modulePath . '/Config') ? $modulePath . '/Config' : $modulePath . '/config';

        if (File::exists($configPath)) {
            foreach (File::files($configPath) as $file) {
                $configName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $this->mergeConfigFrom(
                    $file->getPathname(),
                    $moduleNameLower . '.' . $configName
                );
            }
        }

        // --- 2. ROUTES ---
        if (File::exists($modulePath . '/Routes/web.php')) {
            $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        } elseif (File::exists($modulePath . '/routes/web.php')) {
            $this->loadRoutesFrom($modulePath . '/routes/web.php');
        }

        $apiRoutePath = File::exists($modulePath . '/Routes/api.php') ? $modulePath . '/Routes/api.php' : (File::exists($modulePath . '/routes/api.php') ? $modulePath . '/routes/api.php' : null);

        if ($apiRoutePath) {
            Route::prefix('api')
                ->middleware('api')
                ->group(function () use ($apiRoutePath) {
                    require $apiRoutePath;
                });
        }

        // --- 3. VIEWS & TRANSLATIONS ---
        // Xác định folder Resources (Hỗ trợ cả viết hoa/thường)
        $resourcePath = File::exists($modulePath . '/Resources') ? $modulePath . '/Resources' : $modulePath . '/resources';

        // Views
        if (File::exists($resourcePath . '/views')) {
            $this->loadViewsFrom($resourcePath . '/views', $module);
        }

        // Translations
        if (File::exists($resourcePath . '/lang')) {
            $this->loadTranslationsFrom($resourcePath . '/lang', $module);
            $this->loadJSONTranslationsFrom($resourcePath . '/lang');
        }

        // --- 4. HELPERS ---
        if (File::exists($modulePath . '/Helpers')) {
            foreach (File::allFiles($modulePath . '/Helpers') as $file) {
                require_once $file->getPathname();
            }
        }

        // --- 5. MIGRATIONS ---
        if (File::exists($modulePath . '/Database/Migrations')) {
            $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        } elseif (File::exists($modulePath . '/database/migrations')) {
            $this->loadMigrationsFrom($modulePath . '/database/migrations');
        }

        // --- 6. LIVEWIRE COMPONENTS ---
        $livewirePath = $modulePath . '/Livewire'; // Giả sử namespace là Modules\Admin\Livewire

        if (File::exists($livewirePath)) {
            $namespacePrefix = "Modules\\{$module}\\Livewire";

            foreach (File::allFiles($livewirePath) as $file) {
                $relativePath = str_replace($livewirePath, '', $file->getPathname());
                $relativePath = str_replace('.php', '', $relativePath);

                // Chuyển đường dẫn thành namespace: /Products/ProductTable -> \Products\ProductTable
                $classPath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                $fullClass = $namespacePrefix . $classPath;

                if (class_exists($fullClass)) {
                    // Tạo alias: admin.products.product-table
                    // Logic: Lấy tên module + path + tên file (kebab case)
                    $aliasParts = explode(DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
                    $alias = collect($aliasParts)
                        ->map(fn($part) => Str::kebab($part))
                        ->implode('.');

                    $componentAlias = $moduleNameLower . '.' . $alias;

                    // Đăng ký với Livewire
                    Livewire::component($componentAlias, $fullClass);
                }
            }
        }

        // --- 7. BLADE COMPONENTS (Quan trọng) ---

        // A. Class Based Components (Có file PHP xử lý)
        // Đường dẫn chuẩn Laravel thường là View/Components, nhưng bạn dùng Http/Components thì giữ nguyên
        $classComponentPath = $modulePath . '/View/Components';
        if (!File::exists($classComponentPath)) {
             $classComponentPath = $modulePath . '/Http/Components';
        }

        if (File::exists($classComponentPath)) {
            // Namespace này phải khớp với PSR-4 trong composer.json hoặc folder structure
            // Ví dụ: Modules\Admin\View\Components
            $namespace = "Modules\\{$module}\\View\\Components";
            if(str_contains($classComponentPath, 'Http')) {
                 $namespace = "Modules\\{$module}\\Http\\Components";
            }

            Blade::componentNamespace($namespace, $moduleNameLower);
        }

        // B. Anonymous Components (Chỉ có file view .blade.php)
        // Đường dẫn: Modules/Admin/Resources/views/components
        $anonymousComponentPath = $resourcePath . '/views/components';

        if (File::exists($anonymousComponentPath)) {
            // Dòng này giúp bạn dùng: <x-admin-ckeditor />
            // Nó tự tìm file ckeditor.blade.php trong folder components của module
            Blade::anonymousComponentPath($anonymousComponentPath, $moduleNameLower);
        }
    }
}
