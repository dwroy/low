<?php
/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */

class Bootstrap extends Yaf\Bootstrap_Abstract
{
	public function _init(Yaf\Dispatcher $dispatcher)
    {
        $session = Yaf\Session::getInstance();
        $config = Yaf\Application::app()->getConfig();
        $dispatcher->getRouter()->addConfig($config->routes);
		Yaf\Registry::set('config', $config);
        Yaf\Registry::set('model', new model\ObjectPool);

        if(!$session->get('uid'))
        {
            $user = $this->getUserFromCookie($dispatcher->getRequest());
            if($user) $session->set('uid', $user->id);
        }
    }

//	public function _initPlugin(Yaf\Dispatcher $dispatcher)
//    {
//		$objSamplePlugin = new SamplePlugin();
//		$dispatcher->registerPlugin($objSamplePlugin);
//	}

    private function getUserFromCookie($request)
    {
        $remember = Yaf\Registry::get('config')->get('security.remember_me');
        $duration = $remember->duration * 24 * 3600;
        $key = $request->getCookie($remember->key);
        $keys = explode('_', $key);

        if(count($keys) === 3)
        {
            $user = Yaf\Registry::get('model')->get('User')->find($keys[2]);

            if($key === $user->getAuthorizedKey($keys[1])
                    && $keys[1] + $duration > time()) return $user;
        }

        return null;
    }
}