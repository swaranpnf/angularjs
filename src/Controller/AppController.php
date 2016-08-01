<?php
/**
 * CakePHP(tm) Path File: \App\Controller\AppController.php
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     * Use this method to add common initialization code like loading components.
     * e.g. `$this->loadComponent('Security');`
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        // Set Full Auth via Form
        $this->loadComponent('Auth', [
            'authorize' => ['Controller'],
            'loginRedirect' => [
                'controller' => 'dashboard', // @todo Mi Controller segun PROYECTO a modo de demo
                'action' => 'index',
                'home'
            ],
            'logoutRedirect' => [
                'controller' => 'users', // @todo Mi Controller segun PROYECTO
                'action' => 'logout'
            ],
            'loginAction' => [
                'controller' => 'users', // @todo Mi Controller segun PROYECTO
                'action' => 'login'
            ],
            'authenticate' => [
                'Form' => [
                    //'passwordHasher' => 'Blowfish',
                    'userModel' => 'Users',                                         // @todo Mi TABLA segun DB
                    'fields' => ['username' => 'username', 'password' => 'password'],     // @todo mis campos personalizados segun DB
                  //  'scope' => ['Usuarios.habilitado' => 1]                            // @todo Filtro para bloquiar ingresos de usuarios activos segun DB
                ]
            ],
            'authError' => '¿De verdad crees que se le permita ver eso?',
            'storage' => 'Session'
        ]);
        
    }

    /**
     * @param $usuario
     *
     * @return bool
     */
    public function isAuthorized($usuario = array())
    {
        // Tipo de permiso es Admin..?
        if (true === isset($usuario['perfiles_id']) && $usuario['perfiles_id'] === '1') { // @todo Codigo a cambiar segun DB y PROYECTO
            // Permitir
            return true;
        } else {
            // Denegar
            return false;
        }
    }

    /**
     * @param Event $event An Event instance
     *
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        // Bloquiar todo
        $this->Auth->deny();
        // Es Admin..?
        if ($this->Auth->user('perfiles_id') === 1) { // @todo Codigo a cambiar segun DB y PROYECTO
            // Permitir todo al Admin
            $this->Auth->allow();
        } else {
            // Es anonimo..?
            $this->Auth->allow(['index', 'view', 'display', 'contactarnos', 'registrarce', 'logout']); // @todo Codigo a cambiar segun DB y PROYECTO
        }
    }


    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     *
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
}