<?php

class Router {
    protected $routes = [];

    public function __construct() {
        $this->routes = [
            // Admin Routes
            'admin/dashboard' => ['AdminController', 'dashboard'],
            'admin/doctors' => ['AdminController', 'doctors'],
            'admin/add_doctor' => ['AdminController', 'add_doctor'],
            'admin/store_doctor' => ['AdminController', 'store_doctor'],
            'admin/delete_doctor' => ['AdminController', 'delete_doctor'],
            'admin/regions' => ['AdminController', 'regions'],
            'admin/hospitals' => ['AdminController', 'hospitals'],
            'admin/clinics' => ['AdminController', 'clinics'],
            
            // Blog Management Routes
            'admin/blogs' => ['BlogController', 'index'],
            'admin/blog_add' => ['BlogController', 'add'],
            'admin/blog_store' => ['BlogController', 'store'],
            'admin/blog_edit' => ['BlogController', 'edit'],
            'admin/blog_update' => ['BlogController', 'update'],
            'admin/blog_delete' => ['BlogController', 'delete'],
            
            // Blog Category Routes
            'admin/blog_categories' => ['BlogController', 'categories'],
            'admin/blog_category_add' => ['BlogController', 'addCategory'],
            'admin/blog_category_store' => ['BlogController', 'storeCategory'],
            'admin/blog_category_edit' => ['BlogController', 'editCategory'],
            'admin/blog_category_update' => ['BlogController', 'updateCategory'],
            'admin/blog_category_delete' => ['BlogController', 'deleteCategory'],

            // API
            'blog/api_list' => ['BlogController', 'api_list'],
            'api/regions' => ['ApiController', 'regions'],

            // Default/Auth Routes
            'auth/login' => ['AuthController', 'login'],
            'auth/register' => ['AuthController', 'register'],
            'auth/logout' => ['AuthController', 'logout'],
            'auth/authenticate' => ['AuthController', 'authenticate'],
            'auth/create_user' => ['AuthController', 'create_user'],
            '/' => ['AuthController', 'login'], // Default route redirects to login
        ];
    }

    public function run() {
        $url = isset($_GET['route']) ? $_GET['route'] : '/';
        // Ensure query strings are stripped from route if they appear
        $url = explode('?', $url)[0];
        
        // 1. Check for explicit route mappings
        if (array_key_exists($url, $this->routes)) {
            list($controllerName, $methodName) = $this->routes[$url];
        } else {
            // 2. Fallback to dynamic resolution ([controller]/[method])
            if ($url == '/') {
                list($controllerName, $methodName) = $this->routes['/'];
            } else {
                $parts = explode('/', $url);
                $controllerName = str_replace('_', '', ucwords($parts[0], '_')) . 'Controller';
                $methodName = isset($parts[1]) ? $parts[1] : 'index';
            }
        }

        // Check if controller file exists
        if (file_exists("controllers/" . $controllerName . ".php")) {
            require_once "controllers/" . $controllerName . ".php";
            $controller = new $controllerName();

            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                echo "404 Not Found: Method '$methodName' not found in controller '$controllerName'";
            }
        } else {
            echo "404 Not Found: Controller '$controllerName' not found for route '$url'";
        }
    }
}
