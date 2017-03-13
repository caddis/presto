<?php
namespace Craft;

class PrestoCommand extends BaseCommand
{
	/**
	 * Initiate the action. Looks for any purges that have been added since the check was last run.
	 */
	public function actionCheck()
	{
		craft()->presto->updateRootPath(
			craft()->plugins->getPlugin('presto')->getSettings()['rootPath']
		);

		$lastUpdated = $this->getUpdateTime();
		$this->writeUpdateTime();

		if (! $lastUpdated) {
			craft()->presto->purgeEntireCache();
		} else {
			$lastUpdated = new DateTime($lastUpdated, new \DateTimeZone(craft()->getTimeZone()));

			$paths = craft()->presto->getPurgeEvents($lastUpdated);

			if (count($paths)) {
				if ($paths[0] === 'all') {
					craft()->presto->purgeEntireCache();
				} else {
					craft()->presto->purgeCache($paths);
				}
			}
		}
	}

	/**
	 * Returns the path to the file holding the last update check
	 *
	 * @return string
	 */
	private function getUpdatePath()
	{
		return craft()->path->runtimePath . 'prestoPurgeEvents.txt';
	}

	/**
	 * Returns the string value for when the check was last run
	 *
	 * @return array|bool|string
	 */
	private function getUpdateTime()
	{
		return IOHelper::getFileContents(
			$this->getUpdatePath()
		);
	}

	/**
	 * Updates last update check to the current time, formatted to be equivalent to
	 * DateTimes stored in the database
	 */
	private function writeUpdateTime()
	{
		IOHelper::writeToFile(
			$this->getUpdatePath(),
			DateTimeHelper::formatTimeForDb()
		);
	}
}