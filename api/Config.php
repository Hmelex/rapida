<?php

/**
 * Класс-обертка для конфигурационного файла с настройками магазина
 * В отличие от класса Settings, Config оперирует низкоуровневыми настройками, например найстройками базы данных.
 *
 *
 * @copyright 	2017 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

require_once ('Simpla.php');

class Config
{
	public $version = '0.0.8.1.3';
	
	//соль
	public $salt = 'sale marino. il sale iodato. il sale e il pepe. solo il sale.';
	
	// Файлы для хранения настроек
	public $config_file = '';
	public $db_config_file = '';

	private $vars = array();
	public $vars_sections = array();
	
	// В конструкторе записываем настройки файла в переменные этого класса
	// для удобного доступа к ним. Например: $simpla->config->db_user
	public function __construct()
	{
		dtimer::log(__METHOD__ . " config construct ");
		$this->config_file = dirname(__FILE__) . '/../config/config.ini';
		$this->db_config_file = dirname(__FILE__) . '/../config/db.ini';
		

		// Читаем настройки из файлов с секциями
		$configs = array(
			$this->config_file => parse_ini_file($this->config_file, true, INI_SCANNER_TYPED),
			$this->db_config_file => parse_ini_file($this->db_config_file, true, INI_SCANNER_TYPED)
		);
		
		// Записываем настройки как переменную класса
		//~ var_dump($configs);

		foreach ($configs as $file => $ini) {
			if (is_array($ini)) {
				foreach ($ini as $section => $content) {
					$this->vars_sections[$section] = $content;
					foreach ($content as $name => $value) {
						$this->vars[$name] = array('value' => $value, 'section' => $section, 'file' => $file);
					}
				}
			}
		}
		// Вычисляем DOCUMENT_ROOT вручную, так как иногда в нем находится что-то левое
		$localpath = getenv("SCRIPT_NAME");
		$absolutepath = getenv("SCRIPT_FILENAME");
		$_SERVER['DOCUMENT_ROOT'] = substr($absolutepath, 0, strpos($absolutepath, $localpath));

		// Адрес сайта - тоже одна из настроек, но вычисляем его автоматически, а не берем из файла
		$script_dir1 = realpath(dirname(dirname(__FILE__)));
		$script_dir2 = realpath($_SERVER['DOCUMENT_ROOT']);
		$subdir = trim(substr($script_dir1, strlen($script_dir2)), "/\\");

		// Протокол
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
		if ($_SERVER["SERVER_PORT"] == 443)
			$protocol = 'https';
		elseif (isset($_SERVER['HTTPS']) && ( ($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
			$protocol = 'https';
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
			$protocol = 'https';

		$this->vars['protocol']['value'] = $protocol;
		$this->vars['root_url']['value'] = $protocol . '://' . rtrim($_SERVER['HTTP_HOST']);
		if (!empty($subdir))
			$this->vars['root_url']['value'] .= '/' . $subdir;

		// Подпапка в которую установлена симпла относительно корня веб-сервера
		$this->vars['subfolder']['value'] = $subdir . '/';

		// Определяем корневую директорию сайта
		$this->vars['root_dir']['value'] = dirname(dirname(__FILE__)) . '/';

		// Максимальный размер загружаемых файлов
		$max_upload = (int) (ini_get('upload_max_filesize'));
		$max_post = (int) (ini_get('post_max_size'));
		$memory_limit = (int) (ini_get('memory_limit'));
		$this->vars['max_upload_filesize'] = min($max_upload, $max_post, $memory_limit) * 1024 * 1024;
		
		// Соль для повышения надежности хеширования
		$this->vars['salt'] = md5('sale marino. il sale iodato. il sale e il pepe. solo il sale.');
		
		// Часовой пояс
		if (!empty($this->vars['php_timezone']['value']))
			date_default_timezone_set($this->vars['php_timezone']['value']);
		elseif (!ini_get('date.timezone'))
			date_default_timezone_set('UTC');
	}

	// Магическим методов возвращаем нужную переменную
	public function __get($name)
	{
		if (isset($this->vars[$name]['value'])){
			return $this->vars[$name]['value'];
		}else{
			return null;
		}
	}
	
	// Магическим методов задаём нужную переменную
	public function __set($name, $value)
	{
		# Запишем конфиги
		if (isset($this->vars[$name])) {
			$conf = file_get_contents($this->vars[$name]['file']);
			$conf = preg_replace("/" . $name . "\s*=.*\n/i", $name . ' = ' . $value . ";\r\n", $conf);
			$cf = fopen($this->vars[$name]['file'], 'w');
			fwrite($cf, $conf);
			fclose($cf);
			$this->vars[$name]['value'] = $value;

		}
	}

	public function token($text)
	{
		return md5($text . $this->salt);
	}

	public function check_token($text, $token)
	{
		if (!empty($token) && $token === $this->token($text))
			return true;
		return false;
	}
}
