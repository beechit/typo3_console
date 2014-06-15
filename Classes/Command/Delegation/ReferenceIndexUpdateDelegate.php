<?php
namespace Helhum\Typo3Console\Command\Delegation;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helmut Hummel <helmut.hummel@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Helhum\Typo3Console\Service\Delegation\ReferenceIndexIntegrityDelegateInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\NullWriter;

/**
 * Class ReferenceIndexUpdateDelegate
 */
class ReferenceIndexUpdateDelegate implements ReferenceIndexIntegrityDelegateInterface {

	/**
	 * @var array
	 */
	protected $subscribers = array();

	/**
	 * @param string $name
	 * @param array $arguments
	 */
	public function emitEvent($name, $arguments = array()) {
		if (empty($this->subscribers[$name])) {
			return;
		}

		foreach ($this->subscribers[$name] as $subscriber) {
			call_user_func_array($subscriber, $arguments);
		}
	}

	/**
	 * @param string $name
	 * @param Callback $subscriber
	 */
	public function subscribeEvent($name, $subscriber) {
		if (!isset($this->subscribers[$name])) {
			$this->subscribers[$name] = array();
		}

		$this->subscribers[$name][] = $subscriber;
	}

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @param LoggerInterface $logger
	 */
	function __construct(LoggerInterface $logger = NULL) {
		$this->logger = $logger ?: $this->createNullLogger();
	}

	/**
	 * @param int $unitsOfWorkCount
	 * @return void
	 */
	public function willStartOperation($unitsOfWorkCount) {
		$this->emitEvent('willStartOperation', array($unitsOfWorkCount));
	}

	/**
	 * @param string $tableName
	 * @param array $record
	 * @return void
	 */
	public function willUpdateRecord($tableName, array $record) {
		$this->emitEvent('willUpdateRecord', array($tableName, $record));
	}

	/**
	 * @return void
	 */
	public function operationHasEnded() {
		$this->emitEvent('operationHasEnded');
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * @return LoggerInterface
	 */
	protected function createNullLogger() {
		$logger = new Logger(__CLASS__);
		$logger->addWriter(LogLevel::EMERGENCY, new NullWriter());
		return $logger;
	}
}