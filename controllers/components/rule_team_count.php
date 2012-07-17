<?php
/**
 * Rule helper for returning how many teams a user is on.
 */

class RuleTeamCountComponent extends RuleComponent
{
	var $query_having = 'Person.id HAVING team_count';

	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	function evaluate($params) {
		$date = strtotime ($this->config);
		$count = 0;
		$positions = Configure::read('playing_roster_positions');
		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['position'], $positions) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				strtotime ($team['Division']['open']) <= $date &&
				$date <= strtotime ($team['Division']['close']))
			{
				++ $count;
			}
		}
		return $count;
	}

	function build_query(&$joins, &$fields) {
		$date = date('Y-m-d', strtotime ($this->config));
		$fields['team_count'] = 'COUNT(Team.id) as team_count';
		$joins['TeamsPerson'] = array(
			'table' => 'teams_people',
			'alias' => 'TeamsPerson',
			'type' => 'INNER',
			'foreignKey' => false,
			'conditions' => 'TeamsPerson.person_id = Person.id',
		);
		$joins['Team'] = array(
			'table' => 'teams',
			'alias' => 'Team',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Team.id = TeamsPerson.team_id',
		);
		$joins['Division'] = array(
			'table' => 'divisions',
			'alias' => 'Division',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Division.id = Team.division_id',
		);
		return array(
			'Division.open <=' => $date,
			'Division.close >=' => $date,
			'TeamsPerson.position' => Configure::read('playing_roster_positions'),
			'TeamsPerson.status' => ROSTER_APPROVED,
		);
	}

	function desc() {
		return __('have a team count', true);
	}
}

?>
