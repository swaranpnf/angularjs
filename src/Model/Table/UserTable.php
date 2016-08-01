<?php

// Path File: \App\src\Model\Table\UsuariosTable.php

namespace App\Model\Table;

use App\Model\Entity\Usuario;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DigestAuthenticate;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Usuarios Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Perfiles
 */
class UserTable extends Table {

    function all() {
        $articles = TableRegistry::get('users');
        $query = $articles->find();
        
        return $query;
    }
    
    function changeStatus(){
        $articles = TableRegistry::get('users');
        
    }









    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
//    public function validationDefault(Validator $validator)
//    {
//        $validator
//            ->add('id', 'valid', ['rule' => 'numeric'])
//            ->allowEmpty('id', 'create');
//
//        $validator
//            ->add('email', 'valid', ['rule' => 'email'])
//            ->requirePresence('email', 'create')
//            ->notEmpty('email')
//            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);
//
//        $validator
//            ->requirePresence('password', 'create')
//            ->notEmpty('password');
//
//        $validator
//            ->requirePresence('nombre', 'create')
//            ->notEmpty('nombre');
//
//        $validator
//            ->add('habilitado', 'valid', ['rule' => 'boolean'])
//            ->requirePresence('habilitado', 'create')
//            ->notEmpty('habilitado');
//
//        return $validator;
//    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
//    public function buildRules(RulesChecker $rules)
//    {
//        $rules->add($rules->isUnique(['email']));
//        $rules->add($rules->existsIn(['perfiles_id'], 'Perfiles'));
//        return $rules;
//    }
}
