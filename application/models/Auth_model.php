<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model{

   //get user by id
   public function get_user($id)
   {
       $id = clean_number($id);
       $this->db->where('id', $id);
       $query = $this->db->get('users');
       return $query->row();
   }
      //input values
      public function input_values()
      {
          $data = array(
              'username' => remove_special_characters($this->input->post('username', true)),
              'email' => $this->input->post('email', true),
              'first_name' => ucfirst($this->input->post('first_name', true)),
              'last_name' => ucfirst($this->input->post('last_name', true)),
              'password' => $this->input->post('password', true),
              // 'date_of_birth' => $this->input->post('date_of_birth', true),
              'phone_number' => $this->input->post('phone_number', true),
              'gender' => $this->input->post('gender', true),
            //   'via_sell_now' => $this->input->post('via_sell_now', true)
          );
          return $data;
      }
      //is logged in
      public function is_logged_in()
      {
          //check if user logged in
          if ($this->session->userdata('modesy_sess_logged_in') == true && $this->session->userdata('modesy_sess_app_key') == $this->config->item('app_key')) {
              $user = $this->get_user($this->session->userdata('modesy_sess_user_id'));
              if (!empty($user)) {
                  if ($user->banned == 0) {
                      return true;
                  }
              }
          }
          return false;
      }
          // check for register unique
    public function is_unique_register($email,$phone_number)
    {
        // $query = array($phone_number,$email);
        // {
        // $this->db->where();
        // $this->db->where();
        // }
        $this->db->where('email', $email);
        $this->db->or_where('phone_number', $phone_number);
        // $query=$this->db->where('role !=','member');
        // $query=$this->db->where('user_type !=','registered');
        $query = $this->db->get('users');
        return $query->row();
    }
    
    public function register()
    {
        $this->load->library('bcrypt');

        $data = $this->auth_model->input_values();
        $data['username'] = remove_special_characters($data['email']);
        //secure password
        $data['password'] = $this->bcrypt->hash_password($data['password']);
        $data['role'] = "member";
        $data['user_type'] = "registered";
        // $data["slug"] = $this->generate_uniqe_slug($data["username"]);
        $data['banned'] = 0;
        $data['last_seen'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        // $data['token'] = generate_token();
        // $data['email_status'] = 1;
        // if ($this->general_settings->email_verification == 1) {
        //     $data['email_status'] = 0;
        // }
        // if ($this->general_settings->vendor_verification_system != 1) {
        //     $data['role'] = "vendor";
        // }
        if ($this->db->insert('users', $data)) {
            $last_id = $this->db->insert_id();
            // if ($this->general_settings->email_verification == 1) {
            //     $user = $this->get_user($last_id);
            //     if (!empty($user)) {
                    
            //         // $this->session->set_flashdata('success', trans("msg_register_success") . " " . trans("msg_send_confirmation_email") . "&nbsp;<a href='javascript:void(0)' class='link-resend-activation-email' onclick=\"send_activation_email_register('" . $user->id . "','" . $user->token . "');\">" . trans("resend_activation_email") . "</a>");
            //         // $this->send_email_activation_ajax($user->id, $user->token);
            //     }
            // } else {
                $user = $this->get_user($last_id);

                $user_data = array(
                    'modesy_sess_unique_id' => md5(microtime() . rand()),
                    'modesy_sess_user_id' => $user->id,
                    'modesy_sess_user_email' => $user->email,
                    'modesy_sess_user_role' => $user->role,
                    'modesy_sess_logged_in' => true,
                    'modesy_sess_app_key' => $this->config->item('app_key'),
                );
                $this->session->set_userdata($user_data);

                // $this->save_user_login_session_data();

                // $this->cart_model->add_session_to_cart_in_db($user->id);

                // $user_cart = $this->cart_model->get_user_cart_from_db($user->id);

                // if (!empty($user_cart)) {
                //     $user_cart_id = $user_cart->id;
                //     $cart_details = $this->cart_model->get_cart_details_by_id($user_cart_id);
                //     $this->cart_model->add_cart_to_session_from_db($cart_details, true);
                // }
            // }
            return $last_id;
        } else {
            return false;
        }
    }



     //logout
     public function logout()
     {
         //clear cart
        //  if ($this->auth_user->user_type != "guest") {
        //      $this->cart_model->clear_cart();
        //  }
         //logout from db
        //  $this->delete_user_login_session_data();
         //unset user data
         $this->session->unset_userdata('modesy_sess_user_id');
         $this->session->unset_userdata('modesy_sess_user_email');
         $this->session->unset_userdata('modesy_sess_user_role');
         $this->session->unset_userdata('modesy_sess_logged_in');
         $this->session->unset_userdata('modesy_sess_app_key');
         $this->session->unset_userdata('modesy_sess_user_shiprocket_token');
         $this->session->unset_userdata('modesy_sess_user_location');
     }

         //get user by email register
    public function get_user_by_email_register($email)
    {
        // $this->db->where("user_type!=", "guest");
        $this->db->where('email', $email);
        $query = $this->db->get('users');
        return $query->row();
    }

      //get user by mobile number
      public function get_user_by_mobile($mobile)
      {
          $this->db->where('phone_number', $mobile);
          $query = $this->db->get('users');
          return $query->row();
      }

      
    //login
    public function login()
    {

        $this->load->library('bcrypt');

        $data = $this->input_values();
        $user = $this->get_user_by_email_register($data['email']);

        if (!empty($user)) {
            //check master key
            // if ($this->general_settings->master_key === $data['password']) {
            //     //set user data
            //     $user_data = array(
            //         'modesy_sess_unique_id' => md5(microtime() . rand()),
            //         'modesy_sess_user_id' => $user->id,
            //         'modesy_sess_user_email' => $user->email,
            //         'modesy_sess_user_role' => $user->role,
            //         'modesy_sess_logged_in' => true,
            //         'modesy_sess_app_key' => $this->config->item('app_key'),
            //     );
            //     $this->session->set_userdata($user_data);

            //     $this->save_user_login_session_data();

            //     $this->cart_model->add_session_to_cart_in_db($user->id);

            //     $user_cart = $this->cart_model->get_user_cart_from_db($user->id);

            //     if (!empty($user_cart)) {
            //         $user_cart_id = $user_cart->id;
            //         $cart_details = $this->cart_model->get_cart_details_by_id($user_cart_id);
            //         $this->cart_model->add_cart_to_session_from_db($cart_details, true);
            //     }

            //     return $user;
            // }
            //check password
            if (!$this->bcrypt->check_password($data['password'], $user->password)) {
                
                return false;
            }
            // if ($user->email_status != 1) {
            //     $this->session->set_flashdata('error', trans("msg_confirmed_required") . "&nbsp;<a href='javascript:void(0)' class='link-resend-activation-email' onclick=\"send_activation_email('" . $user->id . "','" . $user->token . "');\">" . trans("resend_activation_email") . "</a>");
            //     return false;
            // }
            if ($user->banned == 1) {
                // $this->session->set_flashdata('error', trans("msg_ban_error"));
                return false;
            }
            //set user data
            $user_data = array(
                'modesy_sess_unique_id' => md5(microtime() . rand()),
                'modesy_sess_user_id' => $user->id,
                'modesy_sess_user_email' => $user->email,
                'modesy_sess_user_role' => $user->role,
                'modesy_sess_logged_in' => true,
                'modesy_sess_app_key' => $this->config->item('app_key'),
            );
            $this->session->set_userdata($user_data);

            // $this->save_user_login_session_data();

            // $this->cart_model->add_session_to_cart_in_db($user->id);

            // $user_cart = $this->cart_model->get_user_cart_from_db($user->id);

            // if (!empty($user_cart)) {
            //     $user_cart_id = $user_cart->id;
            //     $cart_details = $this->cart_model->get_cart_details_by_id($user_cart_id);
            //     $this->cart_model->add_cart_to_session_from_db($cart_details, true);
            // }
            return $user;
        } else if (empty($user)) {
            $user = $this->get_user_by_mobile($data['email']);
            if (!empty($user)) {
                //check master key
                // if ($this->general_settings->master_key === $data['password']) {
                //     //set user data
                //     $user_data = array(
                //         'modesy_sess_unique_id' => md5(microtime() . rand()),
                //         'modesy_sess_user_id' => $user->id,
                //         'modesy_sess_user_email' => $user->email,
                //         'modesy_sess_user_role' => $user->role,
                //         'modesy_sess_logged_in' => true,
                //         'modesy_sess_app_key' => $this->config->item('app_key'),
                //     );
                //     $this->session->set_userdata($user_data);
                //     return $user;
                // }
                //check password
                if (!$this->bcrypt->check_password($data['password'], $user->password)) {
                    // $this->session->set_flashdata('error', trans("login_error"));
                    return false;
                }
                // if ($user->email_status != 1) {
                //     $this->session->set_flashdata('error', trans("msg_confirmed_required") . "&nbsp;<a href='javascript:void(0)' class='link-resend-activation-email' onclick=\"send_activation_email('" . $user->id . "','" . $user->token . "');\">" . trans("resend_activation_email") . "</a>");
                //     return false;
                // }
                if ($user->banned == 1) {
                    // $this->session->set_flashdata('error', trans("msg_ban_error"));
                    return false;
                }
                //set user data
                $user_data = array(
                    'modesy_sess_unique_id' => md5(microtime() . rand()),
                    'modesy_sess_user_id' => $user->id,
                    'modesy_sess_user_email' => $user->email,
                    'modesy_sess_user_role' => $user->role,
                    'modesy_sess_logged_in' => true,
                    'modesy_sess_app_key' => $this->config->item('app_key'),
                );
                
                $this->session->set_userdata($user_data);

                // $this->save_user_login_session_data();

                // $this->cart_model->add_session_to_cart_in_db($user->id);

                // $user_cart = $this->cart_model->get_user_cart_from_db($user->id);

                // if (!empty($user_cart)) {
                //     $user_cart_id = $user_cart->id;
                //     $cart_details = $this->cart_model->get_cart_details_by_id($user_cart_id);
                //     $this->cart_model->add_cart_to_session_from_db($cart_details, true);
                // }
                
                return $user;
            } else {
                // $this->session->set_flashdata('error', trans("login_error"));
                return false;
            }
        }
    }
}