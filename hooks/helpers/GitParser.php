<?php

/**
* Класс для парсинга измененных файлов
*/
class GitParser
{
	/**
	 * Достает файлы которые в stage
	 * @return array
	 */
	public static function getStagedFiles()
	{
		$output = array();
		$rc = 0;

		exec('git rev-parse --verify HEAD 2> /dev/null', $output, $rc);

		$against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
		if ($rc == 0)
			$against = 'HEAD';

		exec("git diff-index --cached --name-status $against | egrep '^(A|M)' | awk '{print $2;}'", $output);
		return $output;
	}

	/**
	 * Возврощает все новые строки
	 * @return array
	 */
	public function getDiffLines()
	{
		$output = array();
		$rc = 0;

		exec('git rev-parse --verify HEAD 2> /dev/null', $output, $rc);

		$against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
		if ($rc == 0)
			$against = 'HEAD';

		exec("git diff $against | egrep '^(\+|@@)'", $output);
		return $output;
	}


}