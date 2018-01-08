<?php
	require_once(__DIR__  . "/gdq.php");

	/// Parsing discord commands and a event shorthand
	///
	/// Call DiscordParser::Parse(a, b) if a user types a certain command you want to map this library to.
	/// a is used to determine which event the library should query (sgdq2017, agdq2018)
	/// b is whatever the user typed after the command; the parser defaults to a `main` if no further instructions are sent
	/// Example: User types `!gdq`, `main()` is called. User types `!gdq next` and `next()` is called.
	/// You can use `GDQ\Event\GetLastEventShorthand()` to determine the current/last event shorthand
	class DiscordParser
	{
		private static function main($eventShort, $query)
		{
			// setup internal data
			$event = new GDQ\Event($eventShort, time());
			$currentRun = $event->GetCurrentRun();
			$nextRuns = $event->GetNextRuns();

			$nextGameTitles = array_map(function ($run) {return $run->gameName;}, $nextRuns);

			// setup external view of data
			$nextGamesText = "";
			if (count($nextGameTitles) >= 2)
			{
				$nextGamesText = sprintf(" und %s", array_pop($nextGameTitles));
			}
			$nextGamesText = implode(", ", $nextGameTitles) . $nextGamesText;

			if ($currentRun != null)
			{
				if (count($nextRuns) == 0)
				{
					$nextGamesText = "nichts mehr! : (";
				}
				else
				{
					echo sprintf(
						"Bis circa %s Uhr gibt's %s! Danach %s! Einschalten! https://www.twitch.tv/gamesdonequick",
						date("H:i", $currentRun->endTime),
						$currentRun->gameName,
						$nextGamesText
					);
				}
			}
			else
			{
				if (count($nextRuns) == 0)
				{
					echo "Kein GDQ gerade. Ich finds auch schade. : (";
				}
				else
				{
					$initialRun = array_shift($nextRuns);
					echo sprintf(
						"Aktuell gibt's noch nichts, ab dem %s aber %s und mehr! Vormerken! https://www.twitch.tv/gamesdonequick",
						date("d.m.Y \u\m H:i \U\h\\r", $initialRun->startTime),
						implode(", ", array_map(function ($run) {return $run->gameName;}, $nextRuns))
					);
				}
			}
		}

		private static function next($eventShort, $query)
		{
			// setup internal data
			$event = new GDQ\Event($eventShort, time());
			$nextRuns = $event->GetNextRuns(10);

			if (count($nextRuns) == 0)
			{
				echo "Kein GDQ gerade. Ich finds auch schade. : (";
				return;
			}

			$schedule = array();
			foreach ($nextRuns as $run)
			{
				array_push($schedule, sprintf("%s Uhr: **%s**", date("H:i", $run->startTime), $run->gameName));
			}

			$output = "";
			do
			{
				$output = implode(" | ", $schedule);
				array_pop($schedule);
			} while (strlen($output) > 400);

			echo $output;
		}

		public static function Parse($eventShort, $rawQuery)
		{
			$methodsAvailable = get_class_methods(new self());
			if (strlen($rawQuery) == 0)
			{
				$rawQuery = "main";
			}
			$queryParams = explode(" ", $rawQuery);

			forward_static_call_array(array(get_class(), array_shift($queryParams)), array($eventShort, $queryParams));
		}
	}
?>