<?php

namespace App\Soho\Support;

use Illuminate\View\Factory;

/**
 * SOHO 视图解析：请求命中 /soho* 时，未带命名空间的视图名优先解析到 soho::。
 */
class SohoViewFactory extends Factory
{
    public function make($view, $data = [], $mergeData = [])
    {
        if (is_string($view) && !str_contains($view, '::') && request()->is('soho', 'soho/*')) {
            $path = resource_path('views/soho/' . str_replace('.', '/', $view) . '.blade.php');
            if (is_file($path)) {
                $view = 'soho::' . $view;
            }
        }
        return parent::make($view, $data, $mergeData);
    }
}