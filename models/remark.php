<?php
/**
 * Remark Model
 *
 * @package     plugins.remarkable
 * @subpackage  models
 * @since		1.0
 * @author		Ben Rasmusen <ben@dailypath.com>
 */
class Remark extends AppModel {
	
	/**
	 * Model Name
	 *
	 * @var string
	 */
	public $name = 'Remark';
	
	/**
	 * Model Display Field
	 *
	 * @var string
	 */
	public $displayField = 'title';
	
	/**
	 * Validation Array
	 *
	 * @var array
	 */
	public $validate = array(
		'model' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
				'message' => 'Model must be alphanumeric',
				'allowEmpty' => true
			),
		),
		'foreign_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Foreign_id must be numeric',
				'allowEmpty' => true
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'User_id must be numeric',
				'allowEmpty' => false
			),
		),
		'parent_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Parent_id must be numeric',
				'allowEmpty' => true
			),
		),
	);

	/**
	 * BelongsTo Associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
		'ParentRemark' => array(
			'className' => 'Remark',
			'foreignKey' => 'parent_id'
		)
	);

	/**
	 * HasMany Associations
	 *
	 * @var array
	 */
	public $hasMany = array(
		'ChildRemark' => array(
			'className' => 'Remark',
			'foreignKey' => 'parent_id',
			'dependent' => false
		)
	);

}
?>