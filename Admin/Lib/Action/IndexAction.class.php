<?php

class IndexAction extends CommonAction {

    // 框架首页
    public function index() {
        if (isset($_SESSION [C('USER_AUTH_KEY')])) {
            $flag = false;
            if (isset($_SESSION['Vid']) && M('Village')->where("id=" . $_SESSION['Vid'])->getField('isJD')) {
                $flag = true;
            }
            //显示菜单项
            $menus = array();
            $group = M("group");
            $map['status'] = 1;
            $map['show'] = 1;
            $g_lists = $group->where($map)->field('id,title')->order('sort asc')->select();
            //log::write($group->getLastSql(),LOG::SQL);
            //读取数据库模块列表生成菜单项
            $node = M("Node");
            $where['level'] = 2;
            $where['show'] = 1;
            $where['status'] = 1;
            $accessList = $_SESSION ['_ACCESS_LIST'];
            foreach ($g_lists as $g_list) {
                $menu = array();
                $where ['group_id'] = $g_list['id'];
                $list = $node->where($where)->field('id,name,group_id,title')->order('sort asc')->select();
                //log::write($node->getLastSql(),LOG::SQL);
                foreach ($list as $key => $module) {
                    if (isset($accessList [strtoupper(APP_NAME)] [strtoupper($module ['name'])]) || $_SESSION ['administrator']) {
                        //设置模块访问权限
                        if ($module['name'] == 'DispatchJD' && !$flag) {
                            continue;
                        }
                        if ($module['name'] == 'Taking_import' && $_SESSION['Vid'] <> '355') {
                            continue;
                        }
                        $module['access'] = 1;
                        $menu['item'][$key] = $module;
                    }
                }
                $menu['title'] = $g_list['title'];
                if (!empty($menu['item'])) {
                    $menus[] = $menu;
                }
            }
            //读取数据库模块列表生成菜单项
            if (!empty($_GET ['tag'])) {
                $this->assign('menuTag', $_GET ['tag']);
            }

            $this->assign('menus', $menus);
            Log::write(print_r($menus, true), LOG::INFO);
        }
        C('SHOW_RUN_TIME', false); // 运行时间显示
        C('SHOW_PAGE_TRACE', false);
        $this->display();
    }

}

?>