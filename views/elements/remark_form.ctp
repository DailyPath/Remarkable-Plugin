<?php
/**
 * Remarks Add Form
 *
 * @package     plugins
 * @subpackage  views.elements
 * @since		1.0
 * @author		Ben Rasmusen <ben@dailypath.com>
 * 
 * @param 	string	$model		(required) Model to be remarked upon
 * @param 	string	$foreign_id	(required) Foreign_id of Model Object to be remarked upon
 * @param 	array	$options	(optional) Further optional options detailed below:
 * 			- 	'fields'	Fields to be displayed, key: field name, value: label name
 * 							default: array(
 *										'title' => 'Title', 
 *										'content' => 'Content'
 *									)
 * 			- 	'submit'	Text to be display on submit button
 * 							default: 'Submit'
 * 
 */

$options = array_merge(array(
	'fields' => array(
		'title' => __("Title", true),
		'content' => array(
			'label' => __("Content", true),
			'placeholder' => __("Content", true)
		)
	),
	'submit' => __("Submit", true),
	'wrapper_class' => null,
	'url' => array('controller' => 'courses','action' => 'remark'),
), $options);

?>
<div class="remarks form<?php echo " " . $options['wrapper_class'] ?>">
<?php echo $this->Form->create($model, array('url' => $options['url'], 'class' => 'remark nice'));?>
	<?php
		if (isset($options['fields']['title'])) {
			echo $this->Form->input('title', array('type' => 'text','label' => $options['fields']['title'],'class' => 'large'));
		}
	?>
	<?php
		if (isset($options['fields']['content'])) {
			echo $this->Form->input('content', array('type' => 'textarea','label' => $options['fields']['content']['label'],'placeholder' => $options['fields']['content']['placeholder'],'class' => 'large','rows' => 1,'cols' => 15));
		}
	?>
	<?php
		if (isset($options['fields']['parent_id'])) {
			echo $this->Form->input('parent_id', array('type' => 'hidden','value' => $options['fields']['parent_id']));
		}
	?>
	<?php
		echo $this->Form->input('foreign_id', array('type' => 'hidden','value' => $foreign_id));
		echo $this->Form->button($options['submit'], array('class' => 'pink button small radius'));
	?>
<?php echo $this->Form->end();?>
</div>