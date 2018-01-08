<?php
	namespace GDQ;

	const ROOTURL = "https://gamesdonequick.com/tracker/api/v1";

	class Runner
	{
		const URL = ROOTURL . "search/?type=runner&id=%s";

		public $name = "";
		public $stream = "";
		public $twitter = "";
		public $youtube = "";
		public $donor = 0;
		public $public = "";

		// prevent invariants
		private function __construct() {}

		public static function withID(int $id)
		{
			$content = json_decode(file_get_contents(sprintf(self::URL, $id)), true);

			return self::withData($content);
		}

		public static function withData(array $data)
		{
			$instance = new self();

			$this->name			= $data["fields"]["name"];
			$this->stream		= $data["fields"]["stream"];
			$this->twitter		= $data["fields"]["twitter"];
			$this->youtube		= $data["fields"]["youtube"];
			$this->donor		= $data["fields"]["donor"];
			$this->public		= $data["fields"]["public"];

			return $instance;
		}
	}

	class Run
	{
		const URL = ROOTURL . "search/?type=run&id=%s";

		public $category = "";
		public $coop = false;
		public $console = "";
		public $gameName = "";
		public $endTime = 0;
		public $description = "";
		public $setupTime = 0;
		public $publicTitle = "";
		public $runTime = 0;
		public $startTime = 0;
		public $gameDisplayName = "";
		public $order = -1;
		public $event = -1;
		public $runnerIDs = array();
		public $releaseYear = 0;
		public $runnerData = array();

		// prevent invariants
		private function __construct() {}

		public static function withID(int $id)
		{
			$content = json_decode(file_get_contents(sprintf(self::URL, $id)), true);

			return self::withData($content);
		}

		public static function withData(array $data)
		{
			$instance = new self();

			$instance->category =			$data["fields"]["category"];
			$instance->coop =				$data["fields"]["coop"];
			$instance->console =			$data["fields"]["console"];
			$instance->gameName =			$data["fields"]["name"];
			$instance->endTime =			strtotime($data["fields"]["endtime"]);
			$instance->description =		$data["fields"]["description"];
			$instance->setupTime =			strtotime($data["fields"]["setup_time"]);
			$instance->publicTitle =		$data["fields"]["public"];
			$instance->runTime =			strtotime($data["fields"]["run_time"]);
			$instance->startTime =			strtotime($data["fields"]["starttime"]);
			$instance->gameDisplayName =	$data["fields"]["display_name"];
			$instance->order =				$data["fields"]["order"];
			$instance->event =				$data["fields"]["event"];
			$instance->runnerIDs =			$data["fields"]["runners"];
			$instance->releaseYear =		strtotime($data["fields"]["release_year"]);

			return $instance;
		}

		public function fillRunnerData()
		{
			foreach ($this->runnerIDs as $runnerID)
			{
				array_push($this->runnerData, Runner::withID($runnerID));
			}
		}
	}

	class Event
	{
		const URL = ROOTURL . "/search/?type=run&eventshort=%s";
		private $allRuns = array();
		
		private $now = 0;
		function __construct($eventshort, $now)
		{
			$this->allRuns = json_decode(file_get_contents(sprintf(self::URL, $eventshort)), true);
			$this->allRuns = array_map(function($unparsedRun) {
				return Run::withData($unparsedRun);
			}, $this->allRuns);
			$this->now = $now;
		}

		public static function GetLastEventShorthand()
		{
			$content = json_decode(file_get_contents(ROOTURL . "/search/?type=event"));
			return end($content)->fields->short;
		}

		public function GetCurrentRun()
		{
			$currentEvent = null;
			foreach ($this->allRuns as $run)
			{
				if ($run->startTime <= $this->now && $run->endTime > $this->now)
				{
					return $run;
				}
			}
		}

		public function GetNextRuns($amount = 3)
		{
			$runs = array();
			$foundCurrent = false;
			foreach ($this->allRuns as $run)
			{
				if (count($runs) == $amount)
				{
					return $runs;
				}

				if ($foundCurrent)
				{
					array_push($runs, $run);
				}
				else
				{
					if ($run->startTime <= $this->now && $run->endTime > $this->now)
					{
						$foundCurrent = true;
					}
				}
			}

			if ($this->allRuns[0]->startTime > $this->now)
			{
				$runs = array_slice($this->allRuns, 0, $amount, true);
			}
			return $runs;
		}
	}
?>