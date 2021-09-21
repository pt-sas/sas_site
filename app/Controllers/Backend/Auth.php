<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;

class Auth extends BaseController
{
	protected $table = 'sys_user';

	public function index()
	{
		$this->new_title = 'Login';

		$data = [
			'title'    	=> '' . $this->new_title . '',
			'sess'    	=> session()->get('logged_in')
		];

		echo view('backend/auth/login', $data);
	}

	public function login()
	{
		$validation = \Config\Services::validation();
		$post = $this->request->getVar();

		try {
			if (!$validation->run($post, 'login')) {
				$response =	$this->field->errorValidation($this->table);
			} else {
				$check = $this->access->checkLogin($post);

				if ($check == 0 || $check == 2) {
					$response = message('error', false, 'Wrong Username or Password');
				} else {
					$msg = $check == 1 ? 'Berhasil Login !' : $check;
					$response = message('success', true, $msg);
				}
			}
		} catch (\Exception $e) {
			$response = message('error', false, $e->getMessage());
		}

		return json_encode($response);
	}

	public function logout()
	{
		session()->destroy();
		return redirect()->to(site_url('auth'));
	}
}