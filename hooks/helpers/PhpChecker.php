<?php

/**
* Класс для проверки php файлов
*/
class PhpChecker
{
	public static $errors = [];
	public static function run($files)
	{
		self::$errors = [];
		foreach ($files as $changedFile)
		{
			if(!self::isPHPFile($changedFile)) continue;
			self::checkTrailingSpaces($changedFile);
			self::checkTabs($changedFile);
			self::checkLint($changedFile);
		}
		return self::$errors;
	}

	/**
	 * Проеверка на пробелы в конце строки
	 * @param  string $changedFile file path
	 * @return true или array
	 */
	public static function checkTrailingSpaces($changedFile)
	{
		if(empty(self::$errors['TrailingSpaces']))
			self::$errors['TrailingSpaces'] = [];

		$handle = fopen($changedFile, "r");
		if (!$handle)
		{
			self::$errors['TrailingSpaces'][] = "No read permissions {$changedFile}.";
			return false;
		}

		$lineNumber = 0;
		while (($line = fgets($handle)) !== false)
		{
			$lineNumber++;
			$lastCharIndex = strlen($line)-2;
			if($line[$lastCharIndex] == ' ' || $line[$lastCharIndex] == "\t")
				self::$errors['TrailingSpaces'][] = "File {$changedFile} Line {$lineNumber}";
		}
		fclose($handle);
	}

	/**
	 * Проверка на табуляции вместо пробелов
	 * @return true или array
	 */
	public function checkTabs($changedFile)
	{
		if(empty(self::$errors['Tabs']))
			self::$errors['Tabs'] = [];
		$handle = fopen($changedFile, "r");
		if (!$handle)
		{
			self::$errors['Tabs'][] = "No read permissions {$changedFile}.";
			return false;
		}

		$lineNumber = 0;
		while (($line = fgets($handle)) !== false)
		{
			$line = str_replace("\t", '', $line);
			$lineNumber++;
			if($line[0] == ' ')
				self::$errors['Tabs'][] = "File {$changedFile} Line {$lineNumber}";
		}
		fclose($handle);
	}

	/**
	 * Проверяет на PHP ошибки файлы
	 * @return array или true
	 */
	public function checkLint($changedFile)
	{
		if(empty(self::$errors['Lint']))
			self::$errors['Lint'] = [];
		$errors = [];
		exec("php -l '{$changedFile}' 2> /dev/null", $output,$errorsCount);
		if($errorsCount != 0)
			self::$errors['Lint'] = array_merge(self::$errors['Lint'],$output);
	}

	/**
	 * Проверяет это PHP файл или нет
	 * @param  string $file
	 * @return boolean
	 */
	public static function isPHPFile($file)
	{
		$fileInfo = pathinfo($file);
		if(empty($fileInfo['extension'])) return false;
		return $fileInfo['extension'] == 'php';
	}

}