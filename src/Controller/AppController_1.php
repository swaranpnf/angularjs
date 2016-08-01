<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array('Auth', 'Session', 'Email', 'Cookie', 'RequestHandler', 'Paginator', 'Qimage');
    public $helpers = array('Html', 'Form', 'Js');
    var $uses = array('Admin', 'User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Cookie->type('rijndael');
        $this->Session->read();
        //get the already added settings for logout
        $this->loadModel('SuperadminSetting');
        $this->loadModel('EmployeeDetail');
        if (isset($_SESSION['Auth']['User']['email']) and ! empty($_SESSION['Auth']['User']['email'])) {
            $findLoginEmployee = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.business_email' => @$_SESSION['Auth']['User']['email'],'EmployeeDetail.comp_id'=>$_SESSION['Auth']['User']['id'])));
            $this->set('findLoginEmployee', $findLoginEmployee);
            $this->loadModel('User');
            $loggedUser=$this->User->find('first',array('conditions'=>array('User.id'=>$_SESSION['Auth']['User']['id'])));
            $this->set('loggedUser',$loggedUser);
        }
        $settingsAre = $this->SuperadminSetting->find('first', array('fields' => array('SuperadminSetting.id', 'SuperadminSetting.logout_time', 'SuperadminSetting.not_auto_logout_users')));
        $this->set('settingsAre', $settingsAre);
        //get count of  the added licensed users from the employeee section
        $this->loadModel('EmployeeDetail');
        $getLicensedUsers = $this->EmployeeDetail->find('count', array('conditions' => array('EmployeeDetail.paid_status' => 1)));
        $this->set('licensedUsersCount', $getLicensedUsers);
        //session
        if (!empty($_SESSION['Auth']['User']['id'])) {
            $this->User->recursive = 0;
            $user = $this->User->find('first', array('conditions' => array('User.id' => $_SESSION['Auth']['User']['id'])));
            $this->set('user', $user);
        }
        if (!empty($_SESSION['Auth']['Admin']['id'])) {
            $admin = $this->Admin->findById($_SESSION['Auth']['Admin']['id']);
            $this->set('admin', $admin);
        }
        $this->Auth->userModel = 'Admin';
        if (isset($this->params['prefix']) && $this->params['prefix'] == 'webadmin') {
            $this->Auth->userModel = 'Admin';
            AuthComponent::$sessionKey = 'Auth.Admin';
            $this->Auth->logoutRedirect = $this->Auth->loginAction = array('prefix' => 'webadmin', 'controller' => 'login', 'action' => 'index');
            $this->Auth->loginError = 'Invalid Username/Password Combination!';
            $this->Auth->flash['element'] = 'admin_error';
            $this->Auth->loginRedirect = array('prefix' => 'webadmin', 'controller' => 'admin', 'action' => 'index');
        } else {
            $this->Auth->userModel = 'User';
            $this->Auth->loginAction = array('prefix' => false, 'controller' => 'frontend', 'action' => 'index');
            $this->Auth->logoutRedirect = $this->Auth->loginAction = BASE_URL;
            $this->Auth->loginError = 'Invalid Email/Password Combination!';
            $this->Auth->flashElement = "auth.front.message";
            $this->Auth->loginRedirect = array('controller' => 'dashboard', 'action' => 'index');
        }
    }

    //function for deleting the record

    public function deleteRecord($modelname = null, $recordId = null) {
        $this->loadModel($modelname);
        $delete = $this->$modelname->delete($recordId);
        return $delete;
    }

    public function updateRecord($modelname, $recordId, $status) {
        $this->loadModel($modelname);
        $data['status'] = $status;
        $data['id'] = $recordId;
        return $this->$modelname->save($data);
    }

    public function getSize($size) {
        if ($size > 0) {
            $unit = intval(log($size, 1024));
            $units = array('B', 'KB', 'MB', 'GB');
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $size / pow(1024, $unit), $units[$unit]);
            }
        }
        return $size;
    }

    function get_mime_content_type($filename) {

        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'docx' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

}
