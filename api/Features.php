<?php

require_once('Simpla.php');

/**
 * Class Features
 */
class Features extends Simpla
{
    private $tokeep = array(
        'category_id',
        'feature_id',
        'features',
        'brand_id',
        'product_id',
        'force_no_cache',
        'visible'
    );
    //тут будут хранится значения опций
    public $options;
    //тут будут хранится сами опции
    public $features;


    /**
     * @param array $filter
     * @return array|bool
     */
    function get_features_ids($filter = array())
    {
        dtimer::log(__METHOD__ . ' start');
        //это вариант по умолчанию id=>val
        $col = isset($filter['return']['col']) ? $filter['return']['col'] : 'name';
        $key = isset($filter['return']['key']) ? $filter['return']['key'] : 'id';

        if (isset($this->features[$key . "_" . $col])) {
            dtimer::log(__METHOD__ . ' return class var');
            return $this->features[$key . "_" . $col];
        }

        $in_filter_filter = '';
        if (isset($filter['in_filter'])) {
            $in_filter_filter = $this->db->placehold('AND in_filter=?', intval($filter['in_filter']));
        }

        // Выбираем свойства
        $q = $this->db->placehold("SELECT * FROM __features WHERE 1 $in_filter_filter");
        $q = $this->db->query($q);
        if ($q === false) {
            return false;
        }
        $this->features[$key . "_" . $col] = $this->db->results_array($col, $key);
        dtimer::log(__METHOD__ . ' return');
        return $this->features[$key . "_" . $col];
    }

    /**
     * @param array $filter
     * @return mixed
     */
    function get_features($filter = array())
    {
        dtimer::log(__METHOD__ . ' start');
        $gid_filter = '';
        $category_id_filter = '';
        if (isset($filter['category_id'])) {
            $category_id_filter = $this->db->placehold('AND id in(SELECT feature_id FROM __categories_features AS cf WHERE cf.category_id in(?@))', (array)$filter['category_id']);
        }

        $in_filter_filter = '';
        if (isset($filter['in_filter'])) {
            $in_filter_filter = $this->db->placehold('AND f.in_filter=?', intval($filter['in_filter']));
        }

        if (isset($filter['gid'])) {
            $gid_filter = $this->db->placehold('AND gid in (?@)', (array)$filter['gid']);
        }

        $id_filter = '';
        if (!empty($filter['id'])) {
            $id_filter = $this->db->placehold('AND f.id in(?@)', (array)$filter['id']);
        }

        // Выбираем свойства
        $q = $this->db->placehold("SELECT * FROM __features AS f
			WHERE 1
			$category_id_filter $in_filter_filter $id_filter $gid_filter ORDER BY f.pos");
        $this->db->query($q);
        $res = $this->db->results_array(null, 'id');
        dtimer::log(__METHOD__ . ' return');
        return $res;
    }

    /**
     * @param $id
     * @param null $col
     * @return mixed
     */
    function get_feature($id, $col = null)
    {
        // Выбираем свойство
        $col = $col ? $col : '*';
        $query = $this->db->placehold("SELECT $col FROM __features WHERE id=? LIMIT 1", (int)$id);
        $this->db->query($query);
        return $this->db->result_array();
    }

    /**
     * @param $id
     * @return mixed
     */
    function get_feature_categories($id)
    {
        dtimer::log(__METHOD__ . " start $id");
        $q = $this->db->placehold("SELECT cf.category_id as category_id FROM __categories_features cf
			WHERE cf.feature_id = ?", $id);
        $this->db->query($q);
        $res = $this->db->results_array('category_id');
        return $res;
    }

    /**
     * @return array
     */
    public function get_options_tree()
    {
        dtimer::log(__METHOD__ . " start");
        $groups = array();
        $groups[0] = array('id' => 0, 'name' => '', 'pos' => 0, 'options' => array());

        $groups = array_merge($groups, $this->get_options_groups());
        $opts = $this->get_features();
        if ($opts !== false) {
            foreach ($opts as $o) {
                $groups[$o['gid']]['options'][$o['id']] = $o;
            }
        } else {
            return array();
        }


        return $groups;
    }

    /**
     * @return mixed
     */
    public function get_options_groups()
    {
        dtimer::log(__METHOD__ . " start");
        $q = "SELECT * FROM __options_groups ORDER BY pos";
        $this->db->query($q);
        $res = $this->db->results_array(null, 'id');
        return $res;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function add_option_group($name)
    {
        dtimer::log(__METHOD__ . " start");
        $this->db->query("SELECT MAX(`pos`) as `pos` FROM __options_groups");
        $pos = $this->db->result_array('pos');
        if (!empty_($pos)) {
            $pos = $pos + 1;
        } else {
            $pos = 0;
        }
        $name = trim($name);
        $group = array();
        $group['pos'] = (int)$group['pos'];
        $group['name'] = str_replace("\xc2\xa0" ,'', trim($group['name']));
        $group['pos'] = (int)$group['pos'];
        $q = $this->db->placehold("INSERT INTO __options_groups SET `name` = ?, `pos` = ? ", $name, $pos);
        $this->db->query($q);
        $res = $this->db->results_array(null, 'id');
        return $res;
    }

    /**
     * @param $group
     * @return bool
     */
    public function update_option_group($group)
    {
        dtimer::log(__METHOD__ . " start");
        if (isset($group['id'])) {
            $id = (int)$group['id'];
            unset($group['id']);
        } else {
            dtimer::log(__METHOD__ . " args error", 1);
            return false;
        }
        if (isset($group['name'])) {
            $group['name'] = trim($group['name']);
        }
        if (isset($group['pos'])) {
            $group['pos'] = (int)$group['pos'];
        }
        $q = $this->db->placehold("UPDATE __options_groups SET ?% WHERE id=?", $group, $id);
        return $this->db->query($q);
    }

    /**
     * @param $gid
     * @return mixed
     */
    public function get_option_group($gid)
    {
        dtimer::log(__METHOD__ . " start");
        $q = $this->db->placehold("SELECT * FROM __options_groups WHERE id=? LIMIT 1", intval($gid));
        $this->db->query($q);
        return $this->db->result_array();
    }

    /* Добавляет свойство товара по новой системе
     */

    /**
     * @param $feature
     * @return mixed
     */
    public function add_feature($feature)
    {
        dtimer::log(__METHOD__ . ' start');
        if (is_object($feature)) {
            $feature = (array)$feature;
        }
        //удалим id, если он сюда закрался, при создании id быть не должно
        if (isset($feature['id'])) {
            unset($feature['id']);
        }

        foreach ($feature as $k => $e) {
            if (empty_($e)) {
                unset($feature[$k]);
            } else {
                $feature[$k] = trim($e);
            }
        }
        //если имя не задано - останавливаемся
        if (isset($feature['name'])) {
            $name = str_replace("\xc2\xa0" ,'', $feature['name']);
        } else {
            return false;
        }

        //проверка, чтобы избежать дублирования свойств
        if ($this->db->query("SELECT id FROM __features WHERE 1 AND name = ?", $name)) {
            $res = $this->db->result_array();
            if (!empty_($res['id'])) {
                return false;
            }
        }


        //используем транслит собственного приготовления
        if (!isset($feature['trans'])) {
            $feature['trans'] = translit_ya($feature['name']);
        }

        //вытаскиваем макс позицию из свойств
        $q = $this->db->query("SELECT MAX(pos) as pos FROM __features");
        if ($q !== false) {
            //макс. позиция в таблице
            $pos = $this->db->result_array('pos');
        }
        //если что-то есть на выходе, делаем $pos = 0, иначе $pos++
        if (isset($pos) && $pos !== null) {
            $feature['pos'] = $pos + 1;
        } else {
            $feature['pos'] = 0;
        }

        $query = $this->db->placehold("INSERT INTO __features SET ?%", $feature);
        dtimer::log(__METHOD__ . " query: $query");

        //прогоняем запрос (метод query в случае успеха выдает true)
        if ($this->db->query($query) !== true) {
            return false;
        }
        $id = $this->db->insert_id();

        /*
         * Тут часть, касающаяся таблицы со свойствами
         */

        //сначала проверим, есть ли целевая таблица, создадим ее, если ее еще нет
        if (!$this->db->query("SELECT 1 FROM __options LIMIT 1")) {
            $this->db->query("CREATE TABLE `s_options` (
				 `id` int(11) NOT NULL AUTO_INCREMENT,
				 PRIMARY KEY (`id`),
				) ENGINE=MyISAM DEFAULT CHARSET=utf8
				");
        }
        if (!$this->db->query("SELECT 1 FROM __options_uniq LIMIT 1")) {
            $this->db->query("CREATE TABLE __options_uniq (`id` INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT, `val` VARCHAR(1024) NOT NULL, `val` VARCHAR(1024) NOT NULL, `md4` BINARY(16) UNIQUE KEY NOT NULL) ENGINE=MyISAM CHARSET=utf8");
            // добавим 1 строку для значения по умолчанию
            $val = '';
            $trans = '';
            $optionhash = hash('md4', translit_ya($val));
            $this->db->query("INSERT INTO __options_uniq SET `id` = 0, `val`= '$val', `trans` = '$trans', `md4` = 0x$optionhash ");

        }
        if (!$this->db->query("SELECT `$id` FROM __options LIMIT 1")) {
            $this->db->query("ALTER TABLE __options ADD `$id` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'");
            //делаем индекс, только если это свойство будет в фильтре
            if (isset($feature['in_filter']) && (bool)$feature['in_filter'] === true) {
                $this->db->query("ALTER TABLE __options ADD INDEX `$id` (`$id`)");
            }
        }
        return $id;
    }


    /**
     * @param $id
     * @param $feature
     * @return bool|int
     */
    public function update_feature($id, $feature)
    {
        $id = (int)$id;
        if (is_object($feature)) {
            $feature = (array)$feature;
        }

        if (isset($feature['id'])) {
            unset($feature['id']);
        }

        if (!empty($feature['name']) && empty($feature['trans'])) {
            $feature['trans'] = translit_ya($feature['name']);
        }

        foreach ($feature as $k => $e) {
            if (empty_($e)) {
                unset($feature[$k]);
            } else {
                $feature[$k] = trim($e);
            }
        }
        if(count($feature) === 0){
            dtimer::log(__METHOD__." nothing to change - feature is empty! abort. ",1);
            return false;
        }

        $this->db->query("UPDATE __features SET ?% WHERE id = ?", $feature, $id);
        if (isset($feature['in_filter']) && (bool)$feature['in_filter'] === true) {
            $this->db->query("ALTER TABLE __options ADD INDEX `$id` (`$id`, `product_id`)");
        } else {
            $this->db->query("ALTER TABLE __options DROP INDEX `$id` ");
        }
        return $id;
    }


    /**
     * @param $id
     */
    public function delete_feature($id)
    {
        $this->db->query("DELETE FROM __features WHERE id=? LIMIT 1", intval($id));
        $this->db->query("ALTER TABLE __options DROP ?!", (int)$id);
        $this->db->query("DELETE FROM __categories_features WHERE feature_id=?", (int)$id);
    }

    /**
     * @param $id
     */
    public function delete_options($id)
    {
        $this->db->query("DELETE FROM __options WHERE product_id=?", (int)$id);
    }


    /**
     * @param $product_id
     * @param $feature_id
     * @param $val
     * @return bool
     */
    public function update_option($product_id, $feature_id, $val)
    {
        dtimer::log(__METHOD__ . " arguments '$product_id' '$feature_id' '$val'");
        if (!isset($product_id) || !isset($feature_id) || !isset($val)) {
            dtimer::log(__METHOD__ . " arguments error 3 args needed '$product_id' '$feature_id' '$val'", 1);
            return false;
        }

        //получим значение для записи в таблицу options из таблицы s_options_uniq
        //сделаем хеш
        trim((string)$val);
        $val = str_replace("\xc2\xa0" ,'',$val);
        $fid = (int)$feature_id;
        $pid = (int)$product_id;
        $trans = translit_ya($val);
        //Хеш будем получать не по чистому значению $val, а по translit_ya($val), чтобы можно было из ЧПУ вернуться к хешу
        $optionhash = hash('md4', $trans);
        $this->db->query("SELECT `id` FROM __options_uniq WHERE `md4`= 0x$optionhash ");

        //Если запись уже есть - продолжаем работу, если нет добавляем запись в таблицу
        if ($this->db->affected_rows() > 0) {
            $vid = $this->db->result_array('id');
        } else {
            $q = $this->db->query("INSERT INTO __options_uniq SET `val`= ?, `trans` = ?, `md4` = 0x$optionhash ", $val, $trans);
            if ($q !== false) {
                $vid = $this->db->insert_id();
            } else {
                dtimer::log(__METHOD__ . " unable to insert row", 1);
                return false;
            }
        }

        $query = $this->db->placehold(
            "INSERT INTO __options SET `product_id` = ? , ?! = ?
		ON DUPLICATE KEY UPDATE ?! = ?",
            $pid,
            $fid,
            $vid,
            $fid,
            $vid
        );
        if ($this->db->query($query)) {
            return $vid;
        } else {
            return false;
        }
    }

    /*
     * Этот метод позволяет писать свойства товаров напрямую, минуя таблицу options_uniq
     * в которой содержатся уникальные значения свойств и их id.
     * Тут $value должен быть сразу в виде числа с id значения из таблицы options_uniq
     */
    /**
     * @param $product_id
     * @param $feature_id
     * @param $value
     * @return bool|int
     */
    public function update_option_direct($product_id, $feature_id, $value)
    {
        if (!isset($product_id) || !isset($feature_id) || !isset($value)) {
            dtimer::log(__METHOD__ . " arguments error 3 args needed '$product_id' '$feature_id' '$value'", 1);
            return false;
        }

        $fid = (int)$feature_id;
        $pid = (int)$product_id;
        $vid = (int)$value;

        $query = $this->db->placehold(
            "INSERT INTO __options SET `product_id` = ? , ?! = ?
		ON DUPLICATE KEY UPDATE ?! = ?",
            $pid,
            $fid,
            $vid,
            $fid,
            $vid
        );

        if ($this->db->query($query)) {
            return $vid;
        } else {
            return false;
        }

    }

    /*
     * Этот метод сделан для быстрого импорта в таблицу опций, за 1 запрос добавляются
     * сразу несколько значений
     */
    /**
     * @param $filter
     * @return bool
     */
    public function update_options_direct($filter)
    {
        dtimer::log(__METHOD__ . ' start');

        if (!isset($filter['product_id'])) {
            dtimer::log(__METHOD__ . ' args error - pid', 1);
            return false;
        } else {
            $pid = (int)$filter['product_id'];
        }

        if (!isset($filter['features']) || !is_array($filter['features']) || empty($filter['features'])) {
            dtimer::log(__METHOD__ . ' args error - features', 1);
            return false;
        } else {
            $features = $filter['features'];
        }

        $set_options = $this->db->placehold("?%", $features);
        $q = $this->db->placehold("INSERT INTO __options 
		SET `product_id` = ? , $set_options ON DUPLICATE KEY UPDATE $set_options", $pid);


        if ($this->db->query($q)) {
            dtimer::log(__METHOD__ . ' end ok');
            return true;
        } else {
            dtimer::log(__METHOD__ . ' end error', 1);
            return false;
        }

    }


    /**
     * @param $id
     * @param $category_id
     */
    public function add_feature_category($id, $category_id)
    {
        $query = $this->db->placehold("INSERT IGNORE INTO __categories_features SET feature_id=?, category_id=?", $id, $category_id);
        $this->db->query($query);
    }


    /**
     * @param $id
     * @param $categories
     * @return bool
     */
    public function update_feature_categories($id, $categories)
    {
        $id = intval($id);
        $query = $this->db->placehold("DELETE FROM __categories_features WHERE feature_id=?", $id);
        $this->db->query($query);


        if (is_array($categories)) {
            $values = array();
            foreach ($categories as $category)
                $values[] = "($id , " . intval($category) . ")";

            $query = $this->db->placehold("INSERT INTO __categories_features (feature_id, category_id) VALUES " . implode(', ', $values));
            return $this->db->query($query);
        } else {
            return false;
        }
    }


    /**
     * @param array $filter
     * @return array
     */
    public function get_options_uniq($filter = array())
    {

        //сначала уберем из фильтра лишние параметры, которые не влияют на результат, но влияют на хэширование
        dtimer::log(__METHOD__ . " start filter: " . var_export($filter, true));
        $filter = array_intersect_key($filter, array_flip($this->tokeep));
        dtimer::log(__METHOD__ . " filtered filter: " . var_export($filter, true));
        $filter_ = $filter;
        if (isset($filter_['force_no_cache'])) {
            $force_no_cache = true;
            unset($filter_['force_no_cache']);
        }


        //сортируем фильтр, чтобы порядок данных в нем не влиял на хэш
        ksort($filter_);
        $filter_string = var_export($filter_, true);
        $keyhash = hash('md4', 'get_options_uniq' . $filter_string);

        //если запуск был не из очереди - пробуем получить из кеша
        if (!isset($force_no_cache)) {
            dtimer::log(__METHOD__ . " normal run keyhash: $keyhash");
            $res = $this->cache->get_cache_nosql($keyhash);


            //запишем в фильтр параметр force_no_cache, чтобы при записи задания в очередь
            //функция выполнялась полностью
            $filter_['force_no_cache'] = true;
            $filter_string = var_export($filter_, true);
            dtimer::log(__METHOD__ . " add task force_no_cache keyhash: $keyhash");

            $task = '$this->features->get_options_uniq(';
            $task .= $filter_string;
            $task .= ');';
            $this->queue->addtask($keyhash, isset($filter['method']) ? $filter['method'] : '', $task);
        }

        if (isset($res) && !empty_($res)) {
            dtimer::log(__METHOD__ . " return cache res count: " . count($res));
            return $res;
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $ids = null;
        $reverse = false;
        $id_filter = '';
        if (isset($filter['ids'])) {
            $id_filter = $this->db->placehold(" AND `id` in ( ?@ )", (array)$filter['ids']);
        }

        $this->db->query("SELECT * FROM __options_uniq WHERE 1 $id_filter");
        if ($reverse === true) {
            $res = $this->db->results_array(null, 'id', true);
        } else {
            $res = $this->db->results_array(null, 'val', true);
        }

        dtimer::log("set_cache_nosql key: $keyhash");
        $this->cache->set_cache_nosql($keyhash, $res);
        dtimer::log(__METHOD__ . ' return db');
        return $res;
    }


    /**
     * @param array $filter
     * @return mixed
     */
    public function get_options_ids($filter = array())
    {
        dtimer::log(__METHOD__ . " start");
        dtimer::log(__METHOD__ . " filter: " . var_export($filter, true));

        //это вариант по умолчанию id=>val
        $col = isset($filter['return']['col']) ? $filter['return']['col'] : 'val';
        $key = isset($filter['return']['key']) ? $filter['return']['key'] : 'id';

        //выводим из сохраненного массива, если у нас не заданы фильтры по id и md4 и не включен force_no_cache
        if (empty($filter['force_no_cache']) && !isset($filter['id']) && !isset($filter['md4']) && !isset($filter['md42'])) {

            if (isset($this->options[$key . "_" . $col])) {
                dtimer::log(__METHOD__ . " using saved class variable");
                return $this->options[$key . "_" . $col];
            }
        }


        //сначала уберем из фильтра лишние параметры, которые не влияют на результат, но влияют на хэширование
        $filter_ = $filter;
        dtimer::log(__METHOD__ . " start filter: " . var_export($filter_, true));
        unset($filter_['method']);
        if (isset($filter_['force_no_cache'])) {
            $force_no_cache = $filter_['force_no_cache'];
            unset($filter_['force_no_cache']);
        }


        //сортируем фильтр, чтобы порядок данных в нем не влиял на хэш
        ksort($filter_);
        $filter_string = var_export($filter_, true);
        $keyhash = hash('md4', 'get_options_ids' . $filter_string);

        //если запуск был не из очереди - пробуем получить из кеша
        if (!isset($force_no_cache)) {
            dtimer::log(__METHOD__ . " normal run keyhash: $keyhash");
            $res = $this->cache->get_cache_nosql($keyhash);

            //Если у нас был запуск без параметров, сохраним результат в переменную класса.
            if (!isset($filter['id']) && !isset($filter['md4']) && !isset($filter['md42'])) {
                $this->options[$key . "_" . $col] = $res;
            }


            //запишем в фильтр параметр force_no_cache, чтобы при записи задания в очередь
            //функция выполнялась полностью
            $filter_['force_no_cache'] = true;
            $filter_string = var_export($filter_, true);
            dtimer::log(__METHOD__ . " force_no_cache keyhash: $keyhash");

            $task = '$this->features->get_options_ids(';
            $task .= $filter_string;
            $task .= ');';
            $this->queue->addtask($keyhash, isset($filter['method']) ? $filter['method'] : '', $task);
        }

        if (isset($res) && !empty_($res)) {
            dtimer::log(__METHOD__ . " return cache res count: " . count($res));
            return $res;
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        //переменные
        $id_filter = '';
        $md4_filter = '';
        $md42_filter = '';

        if (isset($filter['id']) && count($filter['id']) > 0) {
            $id_filter = $this->db->placehold("AND id in (?@)", $filter['id']);
        }

        if (isset($filter['md4']) && count($filter['md4']) > 0) {
            $md4_filter = $this->db->placehold("AND md4 in (?$)", $filter['md4']);
        }

        if (isset($filter['md42']) && count($filter['md42']) > 0) {
            $md42_filter = $this->db->placehold("AND md42 in (?$)", $filter['md42']);
        }

        $this->db->query("SELECT id, val, trans, HEX(md4) as md4 FROM __options_uniq 
		WHERE 1 
		$id_filter
		$md4_filter
		$md42_filter
		");


        $res = $this->db->results_array($col, $key);


        //Если у нас был запуск без параметров, сохраним результат в переменную класса.
        if (!isset($filter['id']) && !isset($filter['md4'])) {
            dtimer::log(__METHOD__ . " save res to class variable");
            $this->options[$key . "_" . $col] = $res;
        }
        dtimer::log(__METHOD__ . " set_cache_nosql key: $keyhash");
        $this->cache->set_cache_nosql($keyhash, $res);

        dtimer::log(__METHOD__ . " return db ");
        return $res;

    }

    /*
     * Этот метод предоставляет комбинированные данные опций, в т.ч. все возможные опции без учета уже выбранных,
     * доступные для выбора опции с учетом уже выбранных. Т.е. если выбрана страна, например, Россия, другие
     * страны будут также доступны для выбора.
     */
    /**
     * @param array $filter
     * @return array|bool
     */
    public function get_options_mix($filter = array())
    {
        //сначала уберем из фильтра лишние параметры, которые не влияют на результат, но влияют на хэширование
        dtimer::log(__METHOD__ . " start filter: " . var_export($filter, true));
        $filter = array_intersect_key($filter, array_flip($this->tokeep));
        dtimer::log(__METHOD__ . " filtered filter: " . var_export($filter, true));
        $filter_ = $filter;
        if (isset($filter_['force_no_cache'])) {
            $force_no_cache = $filter_['force_no_cache'];
            unset($filter_['force_no_cache']);
        }


        //сортируем фильтр, чтобы порядок данных в нем не влиял на хэш
        ksort($filter_);
        $filter_string = var_export($filter_, true);
        $keyhash = hash('md4', 'get_options_mix' . $filter_string);

        //если запуск был не из очереди - пробуем получить из кеша
        if (!isset($force_no_cache)) {
            dtimer::log(__METHOD__ . " normal run keyhash: $keyhash");
            $res = $this->cache->get_cache_nosql($keyhash);


            //запишем в фильтр параметр force_no_cache, чтобы при записи задания в очередь
            //функция выполнялась полностью
            $filter_['force_no_cache'] = true;
            $filter_string = var_export($filter_, true);
            dtimer::log(__METHOD__ . " force_no_cache keyhash: $keyhash");

            $task = '$this->features->get_options_mix(';
            $task .= $filter_string;
            $task .= ');';
            $this->queue->addtask($keyhash, isset($filter['method']) ? $filter['method'] : '', $task);
        }

        if (isset($res) && !empty_($res)) {
            dtimer::log(__METHOD__ . " return cache res count: " . count($res));
            return $res;
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        //это для результата
        $res = array();

        //это понадобится в любом случае
        //массив id=>значение
        $vals = $this->get_options_ids(array('return' => array('col' => 'val', 'key' => 'id')));

        //массив id=>значение транслитом
        $trans = $this->get_options_ids(array('return' => array('col' => 'trans', 'key' => 'id')));

        //Самый простой вариант - если не заданы фильтры по свойствам
        if (!isset($filter['features'])) {
            $res['filter'] = $this->get_options_raw($filter);
            if ($res['filter'] !== false) {
                foreach ($res['filter'] as $fid => $ids) {
                    $res['full'][$fid] = array(
                        'vals' => array_intersect_key($vals, $res['filter'][$fid]),
                        'trans' => array_intersect_key($trans, $res['filter'][$fid])
                    );
                }
            } else {
                return false;
            }
        } else {
            /*
             * Это фильтрованные результаты. Логика:
             * делается выборка для каждого свойства, исключая заданные опции по этому свойству
             */

            //это результат со всеми заданными $fid
            $filter_ = $filter;
            $res['filter'] = $this->get_options_raw($filter_);

            //тут получим полные результаты для отдельных $fid
            foreach ($filter['features'] as $fid => $vid) {
                //копируем фильтр
                $filter_ = $filter;
                //оставляем только нужный нам $fid
                $filter_['feature_id'] = array($fid);
                //убираем из массива заданных фильтров искомый $fid
                unset($filter_['features'][$fid]);

                $raw = $this->get_options_raw($filter_);
                $res['filter'][$fid] = $raw[$fid];
            }

            //это полный результат, поэтому убираем все фильтры
            $filter_ = $filter;
            unset($filter_['features']);
            $res['full'] = $this->get_options_raw($filter_);

            foreach ($res['full'] as $fid => $ids) {
                $res['full'][$fid] = array(
                    'vals' => array_intersect_key($vals, $res['full'][$fid]),
                    'trans' => array_intersect_key($trans, $res['full'][$fid])
                );
            }
        }

        dtimer::log(__METHOD__ . " set_cache_nosql key: $keyhash");
        $this->cache->set_cache_nosql($keyhash, $res);
        dtimer::log(__METHOD__ . " end");
        return $res;


    }

    /*
     * Этим методом можно получить необработанные данные из таблицы s_options
     * Используется для получения входных данных для метода get_options_mix()
     */


    /**
     * @param array $filter
     * @return array|bool
     */
    public function get_options_raw($filter = array())
    {
        //сначала уберем из фильтра лишние параметры, которые не влияют на результат, но влияют на хэширование
        dtimer::log(__METHOD__ . " start filter: " . var_export($filter, true));
        $filter = array_intersect_key($filter, array_flip($this->tokeep));
        dtimer::log(__METHOD__ . " filtered filter: " . var_export($filter, true));
        $filter_ = $filter;

        if (isset($filter_['force_no_cache'])) {
            $force_no_cache = true;
            unset($filter_['force_no_cache']);
        }


        //сортируем фильтр, чтобы порядок данных в нем не влиял на хэш
        ksort($filter_);
        $filter_string = var_export($filter_, true);
        $keyhash = hash('md4', 'get_options_raw' . $filter_string);

        //если запуск был не из очереди - пробуем получить из кеша
        if (!isset($force_no_cache)) {
            dtimer::log(__METHOD__ . " normal run keyhash: $keyhash");
            $res = $this->cache->get_cache_nosql($keyhash);


            //запишем в фильтр параметр force_no_cache, чтобы при записи задания в очередь
            //функция выполнялась полностью
            $filter_['force_no_cache'] = true;
            $filter_string = var_export($filter_, true);
            dtimer::log(__METHOD__ . " add task force_no_cache keyhash: $keyhash");

            $task = '$this->features->get_options_raw(';
            $task .= $filter_string;
            $task .= ');';
            $this->queue->addtask($keyhash, isset($filter['method']) ? $filter['method'] : '', $task);
        }

        if (isset($res) && !empty_($res)) {
            dtimer::log(__METHOD__ . " return cache res count: " . count($res));
            return $res;
        }


        $product_id_filter = '';
        $category_id_filter = '';
        $visible_filter = '';
        $brand_id_filter = '';
        $features_filter = '';
        $products_join = '';
        $products_join_flag = false;
        $res = array();

        //если у нас не заданы фильтры опций и не запрошены сами опции, будем брать все.
        if (!isset($filter['feature_id']) || count($filter['feature_id']) === 0) {
            $f = $this->get_features_ids(array('in_filter' => 1, 'return' => array('key' => 'id', 'col' => 'id')));
            if ($f !== false) {
                $filter['feature_id'] = $f;
            } else {
                //если у нас нет свойств в фильтре, значит и выбирать нечего
                return false;
            }
        }

        if (isset($filter['features']) && is_array($filter['features'])) {
            $features_ids = array_keys($filter['features']);
            //если в фильтрах свойств что-то задано, но этого нет в запрошенных фильтрах, добавляем.
            foreach ($features_ids as $fid) {
                if (!in_array($fid, $filter['feature_id'])) {
                    $filter['feature_id'][] = $fid;
                }
            }
        }


        //собираем столбцы, которые нам понадобятся для select
        $select = "SELECT " . implode(', ', array_map(function ($a) {
                return '`' . $a . '`';
            }, $filter['feature_id']));

        if (isset($filter['category_id'])) {
            $category_id_filter = $this->db2->placehold(' AND o.product_id in(SELECT DISTINCT product_id from s_products_categories where category_id in (?@))', (array)$filter['category_id']);
        }

        if (isset($filter['product_id'])) {
            $product_id_filter = $this->db2->placehold(' AND o.product_id in (?@)', (array)$filter['product_id']);
        }

        if (isset($filter['brand_id'])) {
            $products_join_flag = true;
            $brand_id_filter = $this->db2->placehold(' AND p.brand_id in (?@)', (array)$filter['brand_id']);
        }

        if (isset($filter['visible'])) {
            $products_join_flag = true;
            $visible_filter = $this->db2->placehold(' AND p.visible=?', (int)$filter['visible']);
        }

        //фильтрация по свойствам товаров
        if (!empty($filter['features'])) {
            foreach ($filter['features'] as $fid => $vids) {
                if (is_array($vids)) {
                    $features_filter .= $this->db->placehold(" AND `$fid` in (?@)", $vids);
                }
            }
        }

        if ($products_join_flag === true) {
            $products_join = "INNER JOIN __products p on p.id = o.product_id";
        }

        $query = $this->db2->placehold("$select
		    FROM __options o
		    $products_join
			WHERE 1 
			$product_id_filter 
			$brand_id_filter 
			$features_filter 
		    $visible_filter
			$category_id_filter
			");

        if (!$this->db2->query($query)) {
            dtimer::log(__METHOD__ . " query error: $query", 1);
            return false;
        }


        //вывод обрабатываем построчно
        while (1) {
            $row = $this->db2->result_array(null, 'pid', true);
            if ($row === false) {
                break;
            }
            //~ $res['pid'][] = $row['pid'];
            //~ unset($row['pid']);

            foreach ($row as $fid => $vid) {
                if ($vid !== null && !isset($res[$fid][$vid])) {
                    $res[$fid][$vid] = '';
                }
            }
        }


        dtimer::log("set_cache_nosql key: $keyhash");
        $this->cache->set_cache_nosql($keyhash, $res);
        dtimer::log(__METHOD__ . ' return db');
        return $res;
    }


    /*
     * Этот метод предназначен для получения данных о свойствах напрямую из таблицы options.
     * Т.е. возвращает не сами значения свойств товаров, а только id этих значений.
     */
    /**
     * @param $product_id
     * @return bool
     */
    public function get_product_options_direct($product_id)
    {

        if (!isset($product_id)) {
            return false;
        } else {
            $product_id = (int)$product_id;
        }

        $this->db->query("SELECT * FROM __options WHERE 1 AND `product_id` = ?", $product_id);
        $res = $this->db->result_array();
        if (isset($res['product_id'])) {
            unset($res['product_id']);
            return $res;
        } else {
            return false;
        }
    }


    /**
     * @param $product_id
     * @return bool
     */
    public function get_product_options($product_id)
    {
        dtimer::log(__METHOD__ . " start");
        if (!isset($product_id)) {
            return false;
        } else {
            $product_id = (int)$product_id;
        }

        $this->db->query("SELECT * FROM __options WHERE 1 AND `product_id` = ?", $product_id);
        $options = $this->db->result_array();

        //Если ничего не нашлось - возвращаем false
        if (isset($options['product_id'])) {
            unset($options['product_id']);
        } else {
            return false;
        }
        //выбираем значений опций из соответствующей таблицы
        if ($this->db->query("SELECT id, val FROM __options_uniq WHERE id in (?@)", $options)) {
            $vals = $this->db->results_array(null, 'id', true);
            foreach ($options as $fid => &$option) {
                if (empty($option)) {
                    unset($options[$fid]);
                } else {
                    if (isset($vals[$option]['val'])) {
                        $option = array('fid' => $fid, 'vid' => $option, 'val' => $vals[$option]['val']);
                    } else {
                        dtimer::log(__METHOD__ . " value " . var_export($option, true) . " not found!", 2);
                        unset($options[$fid]);
                    }
                }
            }
            return $options;
        }
        //если не получилось вернуть $options, вернем false
        return false;
    }
}
