<?php
class Division extends AppModel {
	var $name = 'Division';
	var $displayField = 'name';

	var $validate = array(
		'open' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date for the first game.',
			),
		),
		'close' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date for the last game.',
			),
		),
		'ratio' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'sport.ratio'),
				'message' => 'You must select a valid gender ratio.',
			),
		),
		'roster_deadline' => array(
			'date' => array(
				'rule' => array('date'),
				'allowEmpty' => true,
				'message' => 'You must provide a valid roster deadline.',
			),
		),
		'roster_rule' => array(
			'valid' => array(
				'rule' => array('rule'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'There is an error in the rule syntax.',
			),
		),
		'schedule_type' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.schedule_type'),
				'message' => 'You must select a valid schedule type.',
			),
		),
		'rating_calculator' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.rating_calculator'),
				'message' => 'You must select a valid rating calculator.',
			),
		),
		'allstars' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.allstar'),
				'message' => 'You must select a valid allstar entry option.',
			),
		),
		'exclude_teams' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not teams can be excluded.',
			),
		),
		'email_after' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
			),
		),
		'finalize_after' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
			),
		),
	);

	var $belongsTo = array(
		'League' => array(
			'className' => 'League',
			'foreignKey' => 'league_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'division_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'DivisionGameslotAvailability' => array(
			'className' => 'DivisionGameslotAvailability',
			'foreignKey' => 'division_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'division_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'division_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'divisions_people',
			'foreignKey' => 'division_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Day' => array(
			'className' => 'Day',
			'joinTable' => 'divisions_days',
			'foreignKey' => 'division_id',
			'associationForeignKey' => 'day_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
	);

	function _afterFind ($record) {
		if (array_key_exists ('League', $record[$this->alias])) {
			$league = $record[$this->alias]['League'];
		} else if (array_key_exists ('League', $record)) {
			$league = $record['League'];
		} else {
			$league = array();
		}

		if (array_key_exists ('name', $league)) {
			$record[$this->alias]['league_name'] = trim ($league['name'] . ' ' . $record[$this->alias]['name']);
			$record[$this->alias]['long_league_name'] = trim ($league['long_name'] . ' ' . $record[$this->alias]['name']);
			$record[$this->alias]['full_league_name'] = trim ($league['full_name'] . ' ' . $record[$this->alias]['name']);
		}

		return $record;
	}

	// TODO: Add validation details before rendering, so required fields are properly highlighted
	function beforeValidate() {
		if (array_key_exists ('schedule_type', $this->data['Division'])) {
			$league_obj = AppController::_getComponent ('LeagueType', $this->data['Division']['schedule_type']);
			$this->validate = array_merge ($this->validate, $league_obj->schedulingFieldsValidation());
		}
		if (!Configure::read('scoring.allstars')) {
			unset ($this->validate['allstars']);
		}
		return true;
	}

	function afterSave() {
		if (!empty($this->data['Division']['league_id'])) {
			$league_id = $this->data['Division']['league_id'];
		} else {
			$league_id = $this->field('league_id', array('id' => $this->id));
		}

		// Update this division's league open and close dates, if required
		$this->League->contain();
		$league = $this->League->read(array('open', 'close'), $league_id);

		if (empty($league['League']['open']) || $league['League']['open'] == '0000-00-00') {
			$league['League']['open'] = $this->data['Division']['open'];
		} else {
			$league['League']['open'] = min($league['League']['open'], $this->data['Division']['open']);
		}
		$league['League']['close'] = max($league['League']['close'], $this->data['Division']['close']);
		$this->League->save($league, false);
	}

	function readByDate($date) {
		// Our database has Sunday as 1, but date('w') gives it as 0
		$day = date('w', strtotime ($date)) + 1;

		$this->contain('League', 'Day');
		$divisions = $this->find('all', array(
				'conditions' => array('OR' => array(
					'Division.is_open' => true,
					'Division.open > CURDATE()',
				)),
		));
		return Set::extract("/Day[id=$day]/..", $divisions);
	}

	function readByPlayerId($id, $open = true, $teams = false) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$conditions = array(
			'DivisionsPerson.person_id' => $id,
			'Division.is_open' => $open,
		);

		$contain = array(
			'League',
		);
		if ($teams) {
			$contain[] = 'Team';
		}

		$divisions = $this->find('all', array(
			'conditions' => $conditions,
			'contain' => $contain,
			// By grouping, we get only one record per division, regardless
			// of how many days the division may operate on. Without this,
			// a division that runs on two nights would generate two records
			// here. Nothing that uses this function needs the full list
			// of nights, so it's okay.
			'group' => 'Division.id',
			'order' => 'DivisionsDay.day_id, Division.open',
			'fields' => array(
				'Division.*',
				'League.*',
				'DivisionsPerson.person_id', 'DivisionsPerson.position',
				'DivisionsDay.day_id',
			),
			'joins' => array(
				array(
					'table' => "{$this->tablePrefix}divisions_people",
					'alias' => 'DivisionsPerson',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'DivisionsPerson.division_id = Division.id',
				),
				array(
					'table' => "{$this->tablePrefix}divisions_days",
					'alias' => 'DivisionsDay',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'DivisionsDay.division_id = Division.id',
				),
			),
		));

		$this->addPlayoffs($divisions);

		return $divisions;
	}

	function readFinalizedGames($division) {
		$teams = Set::extract ('/Team/id', $division);
		if (empty($teams)) {
			return array();
		}

		$this->Game->contain (array('GameSlot', 'HomeTeam', 'AwayTeam'));
		$games = $this->Game->find('all', array(
				'conditions' => array(
					'OR' => array(
						'home_team' => $teams,
						'away_team' => $teams,
					),
					'home_score !=' => null,
					'away_score !=' => null,
				),
		));

		// Sort games by date, time and field
		usort ($games, array ('Game', 'compareDateAndField'));
		return $games;
	}

	// This would be better placed in afterFind, to make sure that it always happens,
	// but the required queries mess up containment and cause far too much data to
	// be read. Revisit this limitation with a future version of Cake.
	function addPlayoffs(&$data) {
		if (array_key_exists('Division', $data)) {
			if (array_key_exists ('current_round', $data['Division'])) {
				$data['Division']['is_playoff'] = false;

				$season_divisions = $this->find('all', array(
						'conditions' => array(
							'league_id' => $data['Division']['league_id'],
							'current_round !=' => 'playoff',
						),
						'fields' => 'id',
						'contain' => array('Day'),
				));

				$playoff_divisions = $this->find('all', array(
						'conditions' => array(
							'league_id' => $data['Division']['league_id'],
							'current_round' => 'playoff',
						),
						'fields' => 'id',
						'contain' => array('Day'),
				));

				if ($data['Division']['current_round'] == 'playoff') {
					$data['Division']['sister_divisions'] = Set::extract('/Division/id', $playoff_divisions);
					if (!empty($season_divisions)) {
						$data['Division']['season_divisions'] = Set::extract('/Division/id', $season_divisions);
						$data['Division']['season_days'] = array_unique(Set::extract('/Day/id', $season_divisions));
						$data['Division']['is_playoff'] = true;
					}
				} else {
					$data['Division']['sister_divisions'] = Set::extract('/Division/id', $season_divisions);
					if (!empty($playoff_divisions)) {
						$data['Division']['playoff_divisions'] = Set::extract('/Division/id', $playoff_divisions);
					}
				}
			}
		} else {
			foreach (array_keys($data) as $key) {
				$this->addPlayoffs($data[$key]);
			}
		}
	}

	static function rosterDeadline($division) {
		if ($division['roster_deadline'] === null) {
			return $division['close'];
		}
		return $division['roster_deadline'];
	}

	static function rosterDeadlinePassed($division) {
		return (Division::rosterDeadline($division) < date('Y-m-d'));
	}
}
?>