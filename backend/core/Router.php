<?php

/**
 * ==========================================
 * MedNova MVC Router Class
 * ==========================================
 * This class processes all incoming web requests.
 * It reads the route query parameter (`?route=`), matches it against 
 * predefined routes, or resolves it dynamically to the corresponding
 * controller file and method to execute.
 */
class Router {
    // Array to store pre-registered static routes
    protected $routes = [];

    /**
     * Constructor: Initializes static route maps.
      * Maps clean URLs to their respective controllers and methods.
     */
    public function __construct() {
        $this->routes = [
            // Admin Panel Routes
            'admin/dashboard' => ['AdminController', 'dashboard'], // Admin dashboard view
            'admin/doctors' => ['AdminController', 'doctors'], // Admin doctor listing
            'admin/add_doctor' => ['AdminController', 'add_doctor'], // Admin add doctor form page
            'admin/store_doctor' => ['AdminController', 'store_doctor'], // Admin save doctor API handler
            'admin/delete_doctor' => ['AdminController', 'delete_doctor'], // Admin remove doctor action
            'admin/regions' => ['AdminController', 'regions'], // Admin manage regions page
            'admin/hospitals' => ['AdminController', 'hospitals'], // Admin manage hospitals/clinics
            'admin/clinics' => ['AdminController', 'clinics'], // Admin manage clinics
            
            // Blog Management Routes (Admin Panel)
            'admin/blogs' => ['BlogController', 'index'], // List all blogs
            'admin/blog_add' => ['BlogController', 'add'], // Create blog page
            'admin/blog_store' => ['BlogController', 'store'], // Save blog action
            'admin/blog_edit' => ['BlogController', 'edit'], // Edit blog page
            'admin/blog_update' => ['BlogController', 'update'], // Update blog action
            'admin/blog_delete' => ['BlogController', 'delete'], // Delete blog action
            
            // Blog Category Routes (Admin Panel)
            'admin/blog_categories' => ['BlogController', 'categories'], // List categories
            'admin/blog_category_add' => ['BlogController', 'addCategory'], // Create category form
            'admin/blog_category_store' => ['BlogController', 'storeCategory'], // Save category action
            'admin/blog_category_edit' => ['BlogController', 'editCategory'], // Edit category form
            'admin/blog_category_update' => ['BlogController', 'updateCategory'], // Update category action
            'admin/blog_category_delete' => ['BlogController', 'deleteCategory'], // Delete category action

            // API Service Routes
            'blog/api_list' => ['BlogController', 'api_list'], // Endpoint returning JSON list of blogs
            'api/regions' => ['ApiController', 'regions'], // Endpoint returning JSON list of regions

            // Authentication & Registration Routes
            'auth/login' => ['AuthController', 'login'], // Login view
            'auth/register' => ['AuthController', 'register'], // Register view
            'auth/logout' => ['AuthController', 'logout'], // Logout user session
            'auth/authenticate' => ['AuthController', 'authenticate'], // Login verification handler
            'auth/create_user' => ['AuthController', 'create_user'], // Registration save handler
            '/' => ['AuthController', 'login'], // Default landing page redirecting to Login
        ];
    }

    /**
     * Executes routing logic by finding the correct controller and calling its method.
     */
    public function run() {
        // Read target route from the query string (e.g. ?route=admin/dashboard). Defaults to '/'
        $url = isset($_GET['route']) ? $_GET['route'] : '/';
        // Strip trailing query parameters if they are appended to the route variable
        $url = explode('?', $url)[0];
        
        // Step 1: Check if the route is defined explicitly in our static routes array
        if (array_key_exists($url, $this->routes)) {
            list($controllerName, $methodName) = $this->routes[$url];
        } else {
            // Step 2: Fallback to dynamic resolution if not defined statically
            // Format: [controller]/[method] (e.g., api/hospitals runs ApiController->hospitals())
            if ($url == '/') {
                list($controllerName, $methodName) = $this->routes['/'];
            } else {
                $parts = explode('/', $url);
                // Convert underscores or hyphens in path to CamelCase class names
                $controllerName = str_replace('_', '', ucwords($parts[0], '_')) . 'Controller';
                $methodName = isset($parts[1]) ? $parts[1] : 'index'; // Default method is index()
            }
        }

        // Step 3: Instantiate Controller and execute the action method
        if (file_exists("controllers/" . $controllerName . ".php")) {
            require_once "controllers/" . $controllerName . ".php";
            $controller = new $controllerName();

            // Verify if the method exists on the controller instance
            if (method_exists($controller, $methodName)) {
                $controller->$methodName(); // Execute controller action
            } else {
                echo "404 Not Found: Method '$methodName' not found in controller '$controllerName'";
            }
        } else {
            echo "404 Not Found: Controller '$controllerName' not found for route '$url'";
        }
    }
}

