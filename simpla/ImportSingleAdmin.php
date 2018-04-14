<?PHP
require_once ('api/Simpla.php');

class ImportSingleAdmin extends Simpla
{
    public $import_files_dir = 'simpla/files/import/';
    public $import_file = 'import_single.csv';
    public $allowed_extensions = array('csv', 'gz');

    public function fetch()
    {
        $this->design->assign('import_files_dir', $this->import_files_dir);
        if (!file_exists($this->import_files_dir)){
            mkdir($this->import_files_dir, true);
            $this->cache->htaccessGen($this->import_files_dir);
        }

        if (!is_writable($this->import_files_dir)){
            $this->design->assign('message_error', 'no_permission');
        }

        // Проверяем локаль
        if (setlocale(LC_ALL, 0) != $this->config->locale)
        {
            $this->design->assign('message_error', 'locale_error');
            $this->design->assign('locale', $this->config->locale);
        }


        if ($this->request->method('post') && ($this->request->files("file")))
        {
            $uploaded_name = $this->request->files("file", "tmp_name");
            $temp = tempnam($this->import_files_dir, 'temp_');
            if (!move_uploaded_file($uploaded_name, $temp)){
                $this->design->assign('message_error', 'upload_error');
            }
            //тут пытаемся распаковать файл, если он у нас в gzip
            if($this->is_gzip($temp)){
                $temp2 = $temp;
                $temp = tempnam($this->import_files_dir, 'temp_');
                $gzopen = gzopen($temp2, "r");
                $fopen = fopen($temp, "w");
                //разжимаем куски по 2мб и пишем
                while (!feof($gzopen)) {
                    $data = gzread($gzopen, 2097152);
                    fwrite($fopen, $data);
                }
                fclose($fopen);
                gzclose($gzopen);
                unlink($temp2);
            }

            if (!$this->convert_file($temp, $this->import_files_dir . $this->import_file))
                $this->design->assign('message_error', 'convert_error');
            else
                $this->design->assign('filename', $this->request->files("file", "name"));



            unlink($temp);
        }

        return $this->design->fetch('import_single.tpl');
    }


    private function is_gzip($realpath) {
        $mystery_string = file_get_contents($realpath, null, null, null, 50);

        if(mb_strpos($mystery_string , "\x1f" . "\x8b" . "\x08") !== false){;
            return true;
        }
    }

    private function convert_file($source, $dest)
    {
        // Узнаем какая кодировка у файла
        $teststring = file_get_contents($source, null, null, null, 1000000);

        if (preg_match('//u', $teststring)) // Кодировка - UTF8
        {
            // Просто копируем файл
            return copy($source, $dest);
        }
        else
        {
            // Конвертируем в UFT8
            if (!$src = fopen($source, "r"))
                return false;

            if (!$dst = fopen($dest, "w"))
                return false;

            while ( ($line = fgets($src, 4096)) !== false)
            {
                $line = $this->win_to_utf($line);
                fwrite($dst, $line);
            }
            fclose($src);
            fclose($dst);
            return true;
        }
    }

    private function win_to_utf($text)
    {
        if (function_exists('iconv'))
        {
            return @iconv('windows-1251', 'UTF-8', $text);
        }
        else
        {
            $t = '';
            for ($i = 0, $m = strlen($text); $i < $m; $i++)
            {
                $c = ord($text[$i]);
                if ($c <= 127) {
                    $t .= chr($c);
                    continue;
                }
                if ($c >= 192 && $c <= 207) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 208 && $c <= 239) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 240 && $c <= 255) {
                    $t .= chr(209) . chr($c - 112);
                    continue;
                }
//				if ($c==184) { $t.=chr(209).chr(209); continue; };
//				if ($c==168) { $t.=chr(208).chr(129);  continue; };
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                }; #ё
                if ($c == 168) {
                    $t .= chr(208) . chr(129);
                    continue;
                }; #Ё
                if ($c == 179) {
                    $t .= chr(209) . chr(150);
                    continue;
                }; #і
                if ($c == 178) {
                    $t .= chr(208) . chr(134);
                    continue;
                }; #І
                if ($c == 191) {
                    $t .= chr(209) . chr(151);
                    continue;
                }; #ї
                if ($c == 175) {
                    $t .= chr(208) . chr(135);
                    continue;
                }; #ї
                if ($c == 186) {
                    $t .= chr(209) . chr(148);
                    continue;
                }; #є
                if ($c == 170) {
                    $t .= chr(208) . chr(132);
                    continue;
                }; #Є
                if ($c == 180) {
                    $t .= chr(210) . chr(145);
                    continue;
                }; #ґ
                if ($c == 165) {
                    $t .= chr(210) . chr(144);
                    continue;
                }; #Ґ
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                }; #Ґ

            }
            return $t;
        }
    }

}

