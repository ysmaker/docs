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
			self::checkCodeStyle($changedFile);
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
			if($line[0] == ' ' && (empty($line[1]) || $line[1] != '*') )
				self::$errors['Tabs'][] = "File {$changedFile} Line {$lineNumber}";
		}
		fclose($handle);
	}

	/**
	 * Проверяет на PHP ошибки файлы
	 */
	public static function checkLint($changedFile)
	{
		if(empty(self::$errors['Lint']))
			self::$errors['Lint'] = [];
		$errors = [];
		exec("php -l '{$changedFile}' 2> /dev/null", $output,$errorsCount);
		if($errorsCount != 0)
			self::$errors['Lint'] = array_merge(self::$errors['Lint'],$output);
	}

	/**
	 * Проверки по код стайлу
	 */
	public static function checkCodeStyle($changedFile)
	{
		if(empty(self::$errors['CodeStyle']))
			self::$errors['CodeStyle'] = [];

		$handle     = fopen($changedFile, "r");
		$lineNumber = 0;
		$openPhp    = false;

		while (($line = fgets($handle)) !== false)
		{
			$lineNumber++;
			preg_match_all('/<\?/', $line, $opens, PREG_OFFSET_CAPTURE, 0);
			preg_match_all('/\?>/', $line, $closes, PREG_OFFSET_CAPTURE, 0);
			$opens  = $opens[0];
			$closes = $closes[0];

			$lastOpen  = (!empty($opens)) ? end($opens)[1] : -1;
			$lastClose = (!empty($closes)) ? end($closes)[1] : -1;

			if($openPhp)
				array_unshift($opens, [0,0]);

			if($lastOpen >= 0)
				$openPhp = true;

			if(!$openPhp)
				continue;

			$newLine = '';
			foreach($opens as $index => $open)
			{
				$end = (isset($closes[$index])) ? ($closes[$index][1] + 2) - $open[1] : null;
				$phpCode = mb_substr($line, $open[1], $end);
				$newLine .= $phpCode;
			}
			$line = $newLine;
			$line = preg_replace('/[\'"].*[\'"]/U', '""', $line);

			// Проверка длинны строки
			if(strlen($line) > 100)
				self::$errors['CodeStyle'][] = "the line length can not exceed 100 characters: {$changedFile} Line {$lineNumber}";
			// проврка на старые if
			if (preg_match("/endif|endforeach/i", $line) && !preg_match("/<!--/i", $line))
				self::$errors['CodeStyle'][] = "We dont use 'endif' and 'endforeach': {$changedFile} Line {$lineNumber}";
			// проврка на старые открывающие php скобки
			if (preg_match("/<\?(?!=|php)/", $line))
				self::$errors['CodeStyle'][] = "We dont use short open PHP tags: {$changedFile} Line {$lineNumber}";
			// проврка на перенос фигурной скобки
			if (preg_match("/(if|foreach|function).*\).*{/i", $line))
				self::$errors['CodeStyle'][] = "'{' Must begin in a new line: {$changedFile} Line {$lineNumber}";
			// проврка на название переменных
			if (preg_match('/\$value(!?\W)|\$key(!?\W)|\$array(!?\W)|\$string(!?\W)/i', $line))
				self::$errors['CodeStyle'][] = "You cant use variables like \$value \$key ...: {$changedFile} Line {$lineNumber}";
			// Проверка установки пробелов перед и после аператоров
			if (preg_match('/(?<!^| |=|<|>|!|\?)([^? ]={1,3}|!={1,2}|\+|-[^>]|[^* ]\/[^* ]|[^\/\* ]\*[^\/\* ]|:|[^<]\?[^=>]|[^-? ]>|<|&&|\|\|)|([^?]={1,3}|!={1,2}|\+|-[^>]|[^* ]\/[^* ]|[^\/\* ]\*[^\/\* ]|:|[^<]\?[^>=]|[^-?]>|<|&&|\|\|)(?!$| |=|>|php|\?)/', $line))
				self::$errors['CodeStyle'][] = "Separate operators with spaces: {$changedFile} Line {$lineNumber}";
			// Проверка пробелов и табов в конце строки
			if (preg_match('/( |	)$/', $line))
				self::$errors['CodeStyle'][] = "Do not leave spaces or tabs at the end of the line: {$changedFile} Line {$lineNumber}";

			if($lastClose > $lastOpen)
				$openPhp = false;
		}
		fclose($handle);
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