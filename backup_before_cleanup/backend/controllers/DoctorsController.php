<?php
require_once 'models/Doctor.php';

class DoctorsController extends Controller {
    
    public function __construct() {
        parent::__construct();
        // Publicly accessible, or add auth check if needed
    }

    public function index() {
        $doctorModel = new Doctor($this->db);
        $data['doctors'] = $doctorModel->getAllDoctors();
        $data['page_title'] = "Our Doctors";
        $this->view('doctors/index', $data);
    }

    public function show() {
        if (!isset($_GET['id'])) {
            die("Doctor ID missing");
        }
        $id = $_GET['id'];
        $doctorModel = new Doctor($this->db);
        $doctor = $doctorModel->getDoctorById($id);
        
        if (!$doctor) {
            die("Doctor not found");
        }

        $data['doctor'] = $doctor;
        $data['page_title'] = "Doctor Details - " . $doctor['name'];
        $this->view('doctors/show', $data);
    }
}
