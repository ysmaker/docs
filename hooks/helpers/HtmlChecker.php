<?php

/**
* Класс для проверки HTML файлов
*/
class HtmlChecker
{
	public static $errors = [];
	public static function run($files)
	{
		self::$errors = [];
		foreach ($files as $changedFile)
		{
			if(!self::isHTMLFile($changedFile)) continue;
			self::checkTrailingSpaces($changedFile);
			self::checkTabs($changedFile);
		}
		return self::$errors;
	}

	/**
	 * Проеверка на пробелы в конце строки
	 * @param  string $changedFile file path
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
	 */
	public static function checkTabs($changedFile)
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
	 * Проверяет это PHP файл или нет
	 * @param  string $file
	 * @return boolean
	 */
	public static function isHTMLFile($file)
	{
		$fileInfo = pathinfo($file);
		if(empty($fileInfo['extension'])) return false;
		return $fileInfo['extension'] == 'html';
	}

}