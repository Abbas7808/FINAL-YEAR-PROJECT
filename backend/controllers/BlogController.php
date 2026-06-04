<?php
require_once 'models/Blog.php';
require_once 'models/BlogCategory.php';

class BlogController extends Controller {
    private $blogModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->blogModel = new Blog($this->db);
        $this->categoryModel = new BlogCategory($this->db);
    }

    protected function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            $this->redirect('?route=auth/login');
        }
    }

    // --- BLOGS ---

    public function index() {
        $this->checkAdmin();
        $data['blogs'] = $this->blogModel->getAll();
        $data['page_title'] = "Manage Blogs";
        $this->view('admin/blogs/index', $data);
    }

    public function add() {
        $this->checkAdmin();
        $data['categories'] = $this->categoryModel->getAll();
        $data['page_title'] = "Add New Blog";
        $this->view('admin/blogs/add', $data);
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'];
            $slug = $this->blogModel->generateSlug($title);
            $content = $_POST['content']; // CKEditor content
            $excerpt = $_POST['excerpt'] ?? substr(strip_tags($content), 0, 150);
            $category_id = $_POST['category_id'];
            $author_id = $_SESSION['user_id'];

            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "assets/uploads/blogs/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_' . $slug . '.' . $file_ext;
                $image_path = $target_dir . $file_name;
                move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
            }

            $data = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'category_id' => $category_id,
                'image' => $image_path,
                'author_id' => $author_id
            ];

            if ($this->blogModel->create($data)) {
                $_SESSION['success'] = "Blog created successfully!";
            } else {
                $_SESSION['error'] = "Failed to create blog.";
            }
            $this->redirect('?route=admin/blogs');
        }
    }

    public function edit() {
        $this->checkAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('?route=admin/blogs');
        
        $data['blog'] = $this->blogModel->getById($id);
        $data['categories'] = $this->categoryModel->getAll();
        $data['page_title'] = "Edit Blog";
        $this->view('admin/blogs/edit', $data);
    }

    public function update() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $slug = $this->blogModel->generateSlug($title);
            $content = $_POST['content'];
            $excerpt = $_POST['excerpt'] ?? substr(strip_tags($content), 0, 150);
            $category_id = $_POST['category_id'];

            $data = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'category_id' => $category_id,
                'image' => $_POST['old_image'] ?? ''
            ];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "assets/uploads/blogs/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_' . $slug . '.' . $file_ext;
                $image_path = $target_dir . $file_name;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                    $data['image'] = $image_path;
                }
            }

            if ($this->blogModel->update($id, $data)) {
                $_SESSION['success'] = "Blog updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update blog.";
            }
            $this->redirect('?route=admin/blogs');
        }
    }

    public function delete() {
        $this->checkAdmin();
        $id = $_GET['id'] ?? null;
        if ($id && $this->blogModel->delete($id)) {
            $_SESSION['success'] = "Blog deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete blog.";
        }
        $this->redirect('?route=admin/blogs');
    }

    // --- CATEGORIES ---

    public function categories() {
        $this->checkAdmin();
        $data['categories'] = $this->categoryModel->getAll();
        $data['page_title'] = "Blog Categories";
        $this->view('admin/blog_categories/index', $data);
    }

    public function addCategory() {
        $this->checkAdmin();
        $data['page_title'] = "Add Category";
        $this->view('admin/blog_categories/add', $data);
    }

    public function storeCategory() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->categoryModel->create($_POST['name'])) {
                $_SESSION['success'] = "Category added!";
            }
            $this->redirect('?route=admin/blog_categories');
        }
    }

    public function deleteCategory() {
        $this->checkAdmin();
        $id = $_GET['id'] ?? null;
        if ($id) $this->categoryModel->delete($id);
        $this->redirect('?route=admin/blog_categories');
    }

    // --- API ---

    public function api_list() {
        $blogs = $this->blogModel->getAll();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $blogs]);
        exit();
    }
}
