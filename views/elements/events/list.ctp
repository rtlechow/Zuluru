<?php
$seasons = array_unique(Set::extract('/Division/League/season', $events));
echo $this->element('selector', array('title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)));

$days = Set::extract('/Division/Day[id!=]', $events);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array('title' => 'Day', 'options' => $days));

$play_types = array('team', 'individual');
?>
<table class="list">
<tr>
	<th><?php __('Registration'); ?></th>
	<th><?php __('Cost'); ?></th>
	<th><?php __('Opens on'); ?></th>
	<th><?php __('Closes on'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
$i = 0;
$affiliate_id = null;
foreach ($events as $event):
	if (count($affiliates) > 1 && $event['Event']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $event['Event']['affiliate_id'];
?>
<tr>
	<th colspan="5">
		<h3 class="affiliate"><?php echo $event['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$classes = array();
	if ($i++ % 2 == 0) {
		$classes[] = 'altrow';
	}
	if (in_array($event['EventType']['type'], $play_types)) {
		if (!empty($event['Division']['id'])) {
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $event['Division']['League']['season']));
			$days = Set::combine($event, 'Division.Day.{n}.id', 'Division.Day.{n}.name');
			ksort($days);
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
		} else {
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => array()));
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => array()));
		}
	}
	if (!empty($classes)) {
		$class = ' class="' . implode(' ', $classes) . '"';
	} else {
		$class = '';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link(__($event['Event']['name'], true), array('action' => 'view', 'event' => $event['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$cost = $event['Event']['cost'] + $event['Event']['tax1'] + $event['Event']['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['open']); ?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['close']); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('action' => 'view', 'event' => $event['Event']['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if (Configure::read('registration.register_now')) {
				echo $this->Html->link(__('Register Now', true), array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
