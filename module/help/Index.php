<?php
namespace Module\Help;
use Helper\RequestUtil as R;
/**
 * 网站首页显示模块
 * @author Su Chao<suchaoabc@163.com>
 * @sinc 2011-10-10
 */
class Index extends \Lib\common\Application{
    public function __construct(){
        $tpl = \Lib\common\Template::getSmarty();
        $helpM = new \Helper\Help();
        $result = $helpM->getHelpMenuList();
        $params_all = R::getParams();
        $articleId = isset($params_all->params['id']) ? $params_all->params['id'] : 0;
        $act = isset($params_all->params['act']) ? $params_all->params['act'] : '';
        $copyright_display = R::getParams('type');
        
        $contactus = false;
        if($act == 'center'){
        	$contactus = true;
        }
        $tpl->assign('copyright_display',$copyright_display);
        $tpl->assign('contactus',$contactus);
        $tpl->assign('PcoriesId',$articleId);
        $tpl->assign('menuList',$result);
        
        $tpl->display('help_center_new.htm');
    }
}