<?php

namespace App\Libraries;

use App\Models\M_User;
use App\Models\M_Menu;
use App\Models\M_Submenu;
use App\Models\M_Role;

class Access
{
    protected $request;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = \Config\Services::session();
        $this->request = \Config\Services::request();
    }

    /**
     * check login
     * 0 = username tak ada
     * 1 = sukses
     * 2 = password salah
     * @param unknown_type $post
     * @return boolean
     */
    public function checkLogin($post)
    {
        $user = new M_User();

        $dataUser = $user->detail([
            'username'    => $post['username']
        ])->getRow();

        if ($dataUser) {
            if (password_verify($post['password'], $dataUser->password)) {
                $this->session->set([
                    'sys_user_id'   => $dataUser->sys_user_id,
                    'sys_role_id'   => $dataUser->role,
                    'logged_in'     => TRUE
                ]);
                return 1;
            } else {
                return 2;
            }
        }

        return 0;
    }

    public function checkCrud($uri = null, $field = null, $menu_id = null, $setmenu = null)
    {
        $menu = new M_Menu();
        $submenu = new M_Submenu();
        $role = new M_Role();

        try {
            if (!empty($uri)) {
                // Check uri segment from submenu
                $sub = $submenu->where('url', $uri)->first();

                // Check uri segment from main menu
                $parent = $menu->where('url', $uri)->first();

                // submenu already in submenu
                if (isset($sub)) {
                    // Check submenu is set in menu access
                    $access = $role->detail([
                        'am.sys_submenu_id'     => $sub->sys_submenu_id,
                        'am.sys_role_id'        => session()->get('sys_role_id')
                    ])->getRow();

                    // submenu set in role
                    if ($access)
                        $field = $access->$field;
                    else
                        $field = false;
                } else if (isset($parent)) {
                    // Check menu is set in menu access
                    $access = $role->detail([
                        'am.sys_menu_id'        => $parent->sys_menu_id,
                        'am.sys_role_id'        => session()->get('sys_role_id')
                    ])->getRow();

                    // menu set in role
                    if ($access)
                        $field = $access->$field;
                    else
                        $field = false;
                } else {
                    // not already
                    $field = false;
                }
            } else {
                if ($setmenu === 'parent') {
                    $access = $role->detail([
                        'am.sys_menu_id'        => $menu_id,
                        'am.sys_role_id'        => session()->get('sys_role_id')
                    ])->getRow();

                    if ($access)
                        $field = $access->$field;
                    else
                        $field = false;
                } else {
                    $access = $role->detail([
                        'am.sys_submenu_id'     => $menu_id,
                        'am.sys_role_id'        => session()->get('sys_role_id')
                    ])->getRow();

                    // submenu set in role
                    if ($access)
                        $field = $access->$field;
                    else
                        $field = false;
                }
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        return $field;
    }
}