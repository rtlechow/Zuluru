<?php
$field = array(
	'radio' => 'answer_id',
	'select' => 'answer_id',
	'checkbox' => 'answer_id',
	'text' => 'answer',
	'textbox' => 'answer',
);

if (array_key_exists ('Question', $question)) {
	$details = $question['Question'];
} else {
	$details = $question;
}

$options = array(
	'label' => @$details['question'],
	'required' => @$details['required'] || @$question['QuestionnairesQuestion']['required'],
	'type' => $details['type'],
);
if (array_key_exists ('after', $details)) {
	$options['after'] = $this->Html->para (null, $details['after']);
}

if (isset ($this->data) && array_key_exists ('Response', $this->data)) {
	$responses = $this->data['Response'];
} else {
	$responses = array();
}

switch ($details['type'])
{
	case 'radio':
		$key = Question::_formName($details);
		$options['legend'] = $details['question'];
		$options['options'] = Set::combine ($question['Answer'], '{n}.id', '{n}.answer');
		$item = $this->Form->hidden ("Response.$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("Response.$key.{$field[$details['type']]}", $options);
		break;

	case 'select':
		$key = Question::_formName($details);

		$options['options'] = Set::combine ($question['Answer'], '{n}.id', '{n}.answer');
		$options['empty'] = '---';
		$item = $this->Form->hidden ("Response.$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("Response.$key.{$field[$details['type']]}", $options);
		break;

	case 'checkbox':
		$item = $this->Html->tag ('label', $details['question']);
		foreach ($question['Answer'] as $index => $answer) {
			$ckey = Question::_formName($details, $answer);
			$options['label'] = $answer['answer'];
			$options['value'] = $answer['id'];
			$item .= $this->Form->hidden ("Response.$ckey.question_id", array('value' => $details['id'])) .
				$this->Form->input ("Response.$ckey.{$field[$details['type']]}", $options);
			if (array_key_exists ($ckey, $responses) && array_key_exists ('id', $responses[$ckey])) {
				$item .= $this->Form->hidden ("Response.$ckey.id");
			}
		}
		break;

	case 'text':
		$key = Question::_formName($details);
		$options['size'] = 75;
		$item = $this->Form->hidden ("Response.$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("Response.$key.{$field[$details['type']]}", $options);
		break;

	case 'textbox':
		$key = Question::_formName($details);
		$options['cols'] = 72;
		$item = $this->Form->hidden ("Response.$key.question_id", array('value' => $details['id'])) .
			$this->Form->input ("Response.$key.{$field[$details['type']]}", $options);
		break;

	case 'group_start':
		$item = "<fieldset><legend>{$details['question']}</legend>\n";
		break;

	case 'group_end':
		$item = "</fieldset>\n";
		break;

	case 'description':
		$item = $this->Html->tag ('label', $details['question']);
		break;

	case 'label':
		$item = $this->Html->tag ('label', $details['question']);
		break;
}

if (isset ($key) && array_key_exists ($key, $responses) && array_key_exists ('id', $responses[$key]))
{
	$item .= $this->Form->hidden ("Response.$key.id");
}

echo $item;
?>
