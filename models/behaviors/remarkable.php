<?php
/**
 * Remarkable Behavior attaches to any model that can be remarked upon
 *
 * @package     plugins.remarkable
 * @subpackage  models.behaviors
 * @since		1.0
 * @author		Ben Rasmusen <ben@dailypath.com>
 */
class RemarkableBehavior extends ModelBehavior {
	
	/**
	 * Behavior Settings
	 *
	 * @var array
	 */
	private $_settings = array();
	
	/**
	 * Collection of validation errors
	 *
	 * @var array
	 */
	private $_validationErrors = array();

	/**
	 * Setup method for behavior
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @param	object	$Model	(reference)
	 * @param	array	$settings
	 * @return	void
	 */
	public function setup(&$Model, $settings = array()) {
		
		// DPTODO (ben@dailypath.com): Move some of these into a config file
		$this->_settings[$Model->alias] = array_merge(array(
			'remarksModel' => 'Remarkable.Remark',
			'required' => array(
				'title' => 'A title is required',
				'content' => 'Contents are required'
			),
			'authorModel' => 'User',
			'softDeletable' => array(
				'enabled' => true,
				'settings' => array(
					'field' => 'is_deleted',
					'field_date' => 'modified'
				),
			)
		), (array) $settings);
		// Init the Remark model
		if (empty($this->Remark)) {
			$this->Remark = ClassRegistry::init($this->_settings[$Model->alias]['remarksModel']);
			// Bind the 'author' model
			$this->Remark->bindModel(array('belongsTo' => array(
				$this->_settings[$Model->alias]['authorModel'] => array(
					'className' => $this->_settings[$Model->alias]['authorModel'],
					'foreignKey' => 'user_id'
				)
			)));
			if ($this->_settings[$Model->alias]['softDeletable']['enabled']) {
				$this->Remark->Behaviors->attach('softdeletable.SoftDeletable', $this->_settings[$Model->alias]['softDeletable']['settings']);
			}
		}
		
	}
	
	/**
	 * Save a new remark
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @param	array	$data
	 * @return	boolean
	 */
	public function remark(&$Model, $data=array()) {
		
		$settings = $this->_settings[$Model->alias];
		
		$data = array_merge(array(
			'model' => $Model->alias
		), $data);
		
		// Set which fields are required
		foreach ($settings['required'] as $column => $message) {
			$this->Remark->validate[$column] = array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => $message
				)
			);
		}
		
		if ($this->Remark->save($data)) {
			return $this->Remark->id;
		} else {
			$this->_validationErrors = $this->Remark->invalidFields();
			return false;
		}
		
	}
	
	/**
	 * Return a remark
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @param	mixed	$params
	 * @param	array	$options
	 * @return	mixed
	 */
	public function getRemark(&$Model, $params=null, $options=array()) {
		
		$options = array_merge(array(
			'contain' => array(
				$this->_settings[$Model->alias]['authorModel'], 
				'ParentRemark' => array('conditions' => array('ParentRemark.is_deleted !=' => 1)), 
				'ChildRemark' => array('conditions' => array('ChildRemark.is_deleted !=' => 1))
			)
		), (array) $options);
		// Search on ID if $params is NOT an array and is numeric
		if (!is_array($params) && is_numeric($params)) {
			$options['conditions'] = array("{$this->Remark->alias}.id" => $params);
		} elseif (is_array($params)) { // Else, set the array of params for a custom query
			$options['conditions'] = $params;
		} else { // return an empty array if no search params were sent in
			return array();
		}
		
		return $this->Remark->find('first', $options);
		
	}
	
	/**
	 * Retrieve remarks for a specific Model/foreign_id combination
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @param	object	$Model (reference)
	 * @param	string	$foreign_id
	 * @param	string	$model
	 * @param	array	$options
	 * @return	array
	 */
	public function getRemarks(&$Model, $foreign_id=null, $model=null, $options=array()) {
		
		$model = ($model) ? $model : $Model->alias;
		$author_model = $this->_settings[$Model->alias]['authorModel'];
		
		$options = array_merge(array(
			'contain' => array(
				$author_model, 
				'ChildRemark' => array('conditions' => array('ChildRemark.is_deleted !=' => 1))
			),
			'limit' => 10,
			'order' => "{$this->Remark->alias}.created DESC",
			'conditions' => array(
				"{$this->Remark->alias}.model" => $model,
				"{$this->Remark->alias}.foreign_id" => $foreign_id,
				"{$this->Remark->alias}.parent_id" => null,
			)
		), (array) $options);
		
		$remarks = $this->Remark->find('all', $options);
		
		// This next pieces is only relevant if retrieving ChildRemarks
		if (in_array('ChildRemark', $options['contain']) || !empty($options['contain']['ChildRemark'])) {
			
			// Extracted from top level authors already in memory
			$available_authors = Set::combine($remarks, "/{$author_model}/id", "/{$author_model}");
			
			// Extract user_ids for one big query
			$user_ids = array();
			foreach ($remarks as $key => $remark) {
				$user_ids = array_merge(Set::extract('/ChildRemark/user_id', $remark), $user_ids);
			}
			
			// A unique array of only the authors we don't already have in memory
			$search_ids = array_unique(array_values(array_diff($user_ids, array_keys($available_authors))));
			
			$authors = array_merge($this->Remark->{$author_model}->find('all', array(
				'conditions' => array(
					"{$author_model}.id" => $search_ids
				)
			)), $available_authors);
			
			$authors = Set::combine($authors, "/{$author_model}/id", "/{$author_model}");
			
			// Rejigger the array
			foreach ($remarks as $key => $remark) {
				foreach ($remark['ChildRemark'] as $child_key => $child) {
					$remarks[$key]['ChildRemark'][$child_key] = array_merge(
						array('Remark' => $remarks[$key]['ChildRemark'][$child_key]),
						$authors[$child['user_id']]
					);
				}
			}
			
		}
		
		return $remarks;
		
	}
	
	/**
	 * Delete a remark
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @param	object	$Model (reference)
	 * @param	string	$id
	 * @return	boolean
	 */
	public function deleteRemark(&$Model, $id=null) {
		
		// DPTODO (ben@dailypath.com): Address the weirdness below
		// For some reason soft deleting returns false on success...?
		$this->Remark->delete($id, false);
		
		return true;
		
	}
	
	/**
	 * Return validation errors for Remark model
	 *
	 * @since	1.0
	 * @author	Ben Rasmusen <ben@dailypath.com>
	 * @return	mixed
	 */
	public function getRemarkValidationErrors() {
		return $this->_validationErrors;
	}

}

?>