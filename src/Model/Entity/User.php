<?php

// Path File: \App\src\Model\Entity\Usuario.php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Validation\Validator;

/**
 * Usuario Entity.
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $nombre
 * @property int $perfiles_id
 * @property \App\Model\Entity\Perfile $perfile
 * @property bool $habilitado
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class User extends Entity {

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    public function validationDefault(Validator $validator) {
        $validator
                ->requirePresence('email1', 'create')
                ->notEmpty('email1');

        $validator
        ->allowEmpty('password')
         ->notEmpty('password');

        return $validator;
    }

    protected function _setPassword($password) {
        return (new DefaultPasswordHasher)->hash($password);
    }

}
