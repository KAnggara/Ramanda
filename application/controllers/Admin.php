<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    is_logged_in();
  }

  public function index()
  {
    $data['title'] = 'Dashboard';
    $data['obs'] = $this->db->get('counter')->result_array();
    $data['tobs'] = $this->db->order_by('id', 'DESC')->get('counter')->result_array();
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/index', $data);
    $this->load->view('templates/footer', $data);
  }


  public function role()
  {
    $data['title'] = 'Role';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $data['role'] = $this->db->get('user_role')->result_array();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/role', $data);
    $this->load->view('templates/footer');
  }


  public function roleAccess($role_id)
  {
    $data['title'] = 'Role Access';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();

    $this->db->where('id !=', 1);
    $data['menu'] = $this->db->get('user_menu')->result_array();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/role-access', $data);
    $this->load->view('templates/footer');
  }


  public function changeAccess()
  {
    $menu_id = $this->input->post('menuId');
    $role_id = $this->input->post('roleId');

    $data = [
      'role_id' => $role_id,
      'menu_id' => $menu_id
    ];

    $result = $this->db->get_where('user_access_menu', $data);

    if ($result->num_rows() < 1) {
      $this->db->insert('user_access_menu', $data);
    } else {
      $this->db->delete('user_access_menu', $data);
    }

    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Access Changed!</div>');
  }

  public function config()
  {
    $data['title'] = 'Configuration';
    $data['ddata'] = $this->db->get_where('data', ['id' => '1'])->row_array();
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $this->form_validation->set_rules('warning', 'Warning', 'required|trim');

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('admin/config', $data);
      $this->load->view('templates/footer');
    } else {
      $warning = $this->input->post('warning');
      $dlimit = $this->input->post('dlimit');
      $dupdate = $this->input->post('dupdate');
      // password sudah ok
      $ddata = array(
        'Dlimit' => round($dlimit / 274 * 1000000),
        'Dupdate' => round($dupdate / 274 * 1000),
        'warning' => round($warning / 274 * 1000000)
      );
      $this->db->where('id', 1);
      $this->db->update('data', $ddata);

      $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Config Updated!</div>');
      redirect('admin/config');
    }
  }

  public function reset()
  {
    $data['title'] = 'Configuration';
    $this->load->model('Menu_model', 'ambil');
    $data['sisa'] = $this->ambil->ambilMax();
    $data['hari'] = $this->ambil->sisaHari();
    $data['harimin'] = $this->ambil->hariMin();
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    $this->form_validation->set_rules('warning', 'Warning', 'required|trim');

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('admin/reset', $data);
      $this->load->view('templates/footer');
    } else {
      $data = array(
        'data' => 0,
        'total' => 0,
        'time' => time()
      );
      $this->db->empty_table('counter');
      $this->db->insert('counter', $data);
      $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data has been Reset!</div>');
      redirect('admin/reset');
    }
  }
}
