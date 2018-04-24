<?php

/**
* Класс для проверки php файлов
*/
class PhpChecker
{
	public static function run($files)
	{
		self::checkTrailingSpaces($files);
	}

	/**
	 * Проеверка на пробелы в конце строки
	 * @param  array $changedFiles
	 * @return true или array
	 */
	public static function checkTrailingSpaces($changedFiles)
	{
		echo "<pre>";
		print_r($changedFiles);

	}

}