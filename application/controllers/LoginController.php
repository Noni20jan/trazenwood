<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LoginController extends CI_Controller
{
    // public function __construct()
    // {
    //     parent::__construct();
    // }

      /**
     * Register
     */
    public function register()
    {
        //check if logged in
        // $this->load->helper('custom'); 
        if (auth_check()) {
            redirect(lang_base_url());
        }

        // if ($this->recaptcha_status == true) {
        //     if (!$this->recaptcha_verify_request()) {
        //         $this->session->set_flashdata('form_data', $this->auth_model->input_values());
        //         $this->session->set_flashdata('error', trans("msg_recaptcha"));
        //         redirect($this->agent->referrer());
        //         exit();
        //     }
        // }

        //validate inputs
        // $this->form_validation->set_rules('username', trans("username"), 'required|xss_clean|min_length[4]|max_length[100]');
        $this->form_validation->set_rules('email', trans("email_address"), 'required|xss_clean|max_length[200]');

        // if ($this->form_validation->run() === false) {
        //     $this->session->set_flashdata('errors', validation_errors());
        //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
        //     redirect($this->agent->referrer());
        // } else {
            $email = $this->input->post('email', true);
            $phone_number = $this->input->post('phone_number', true);
            // $username = $this->input->post('username', true);
            $unique_register = $this->auth_model->is_unique_register($email,$phone_number);
            if (empty($unique_register)) {
                // create
                $user_id = $this->auth_model->register();
                if ($user_id) {
                    $user = get_user($user_id);
                    if (!empty($user)) {
                        //update slug
                        // $this->auth_model->update_slug($user->id);
                        // if ($this->general_settings->email_verification != 1) {
                        //     $this->auth_model->login_direct($user);
                        //     $this->session->set_flashdata('success', trans("msg_register_success"));
                        //     // redirect(generate_url(""));
                        // }
                    }
                    $data = array(
                        'result' => 1,
                        'user' => $user,
                        'message'=>'User Registered Successfully'
                    );
                    echo json_encode($data);
                } else {
                    //error
                    // $this->session->set_flashdata('form_data', $this->auth_model->input_values());
                    // $this->session->set_flashdata('error', trans("msg_error"));
                    $data = array(
                        'result' => 0,
                        'message' => "Something went wrong. Please try again!!"
                    );
                    echo json_encode($data);
                    die();
                }
            } else {
              
                    //error message
                    // $this->session->set_flashdata('form_data', $this->auth_model->input_values());
                    // $this->session->set_flashdata('error', trans("already_registered"));
                    $data = array(
                        'result' => 0,
                        'message' => "User Already Registered"
                    );
                    echo json_encode($data);
                    die();
                
            }

            //

            //is email unique
            // if (!$this->auth_model->is_unique_email($email)) {
            //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
            //     $this->session->set_flashdata('error', trans("msg_email_unique_error"));
            //     $data = array(
            //         'result' => 0,
            //         'error_message' => $this->load->view('partials/_messages', '', true)
            //     );
            //     echo json_encode($data);
            // } else if (!$this->auth_model->is_unique_phone($phone_number)) {
            //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
            //     $this->session->set_flashdata('error', trans("msg_phone_unique_error"));
            //     $data = array(
            //         'result' => 0,
            //         'error_message' => $this->load->view('partials/_messages', '', true)
            //     );
            //     echo json_encode($data);
            // } else {
            //register
            // $user_id = $this->auth_model->register();
            // if ($user_id) {
            //     $user = get_user($user_id);
            //     if (!empty($user)) {
            //         //update slug
            //         $this->auth_model->update_slug($user->id);
            //         if ($this->general_settings->email_verification != 1) {
            //             $this->auth_model->login_direct($user);
            //             $this->session->set_flashdata('success', trans("msg_register_success"));
            //             // redirect(generate_url(""));
            //         }
            //     }
            //     $data = array(
            //         'result' => 1,
            //         'user' => $user
            //     );
            //     echo json_encode($data);
            // } else {
            //     //error
            //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
            //     $this->session->set_flashdata('error', trans("msg_error"));
            //     $data = array(
            //         'result' => 0,
            //         'error_message' => $this->load->view('partials/_messages', '', true)
            //     );
            //     echo json_encode($data);
            // }
            // }
            //is username unique
            // if (!$this->auth_model->is_unique_username($username)) {
            //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
            //     $this->session->set_flashdata('error', trans("msg_username_unique_error"));
            //     redirect($this->agent->referrer());
            // }
        // }

        //  $this->load->model("email_model");
        //$this->session->set_flashdata('submit', "send_email");

        // if (1) {
        //     if (!$this->email_model->send_regret_shopmail($user->email,$user->first_name)) {
        //         redirect($this->agent->referrer());
        //        // exit();
        //     }
        //     $this->session->set_flashdata('success', trans("msg_email_sent"));
        // } else {
        //     $this->session->set_flashdata('error', trans("msg_error"));
        // }
    }


      /**
     * Logout
     */
    public function logout()
    {
        // if (!$this->auth_check) {
        //     // redirect(lang_base_url());
        // }
        $this->auth_model->logout();
        $data = array(
            'result' => 1,
            'message' => "Logout"
        );
        echo json_encode($data);
        die();
        // redirect(lang_base_url());
        // redirect($this->agent->referrer());
    }
    
        /**
     * Login Post
     */
    public function login()
    {
        //check auth
        if (auth_check()) {
            $data = array(
                'result' => 1,
                // 'user' => $this->auth_user,
                'message'=> 'User already logged in'
            );
            echo json_encode($data);
            exit();
        }
        //validate inputs
        // $this->form_validation->set_rules('email', trans("email_address"), 'required|xss_clean|max_length[100]');
        // $this->form_validation->set_rules('password', trans("password"), 'required|xss_clean|max_length[30]');
        // if ($this->form_validation->run() == false) {
        //     $this->session->set_flashdata('errors', validation_errors());
        //     $this->session->set_flashdata('form_data', $this->auth_model->input_values());
        //     // $this->load->view('partials/_messages');
        // } else {
            $user = $this->auth_model->login();
            if (!empty($user)) {
                // $this->shiprocket();
                $data = array(
                    'result' => 1,
                    'user' => $user
                );
                echo json_encode($data);
                die();
            } else {
                $data = array(
                    'result' => 0,
                    'message' => 'Wrong Credentials'
                );
                echo json_encode($data);
                die();
            }
            // reset_flash_data();
        // }
    }
}